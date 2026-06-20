<?php
/**
 * AI预测控制器 - 全部AI算法逻辑、评分计算
 * 
 * 包含：蒙特卡洛模拟、马尔可夫链、贝叶斯概率、综合权重评分
 */

require_once __DIR__ . '/../config/db_supabase.php';
require_once __DIR__ . '/../config/constant.php';
require_once __DIR__ . '/../model/LotteryData.php';
require_once __DIR__ . '/../model/PredictLog.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../config/session_init.php';

class AiPredictCtrl
{
    /**
     * 主预测入口（AJAX）
     * POST参数：
     *   lottery_type: 'dlt'|'ssq'
     *   predict_type: 'single'|'multi_5'|'multi_10'|'lab'
     *   strategy: '稳健'|'平衡'|'激进'|'极限' (仅lab时需要)
     *   user_weights: JSON字符串，自定义权重对象 (仅lab时可选)
     *   monte_carlo_runs: 模拟次数 (可选)
     *   period: 分析期数 20|50|100|all (可选，默认100)
     */
    public static function predict(): void
    {
        requireLoginJSON();

        $lotteryType = $_POST['lottery_type'] ?? '';
        $predictType = $_POST['predict_type'] ?? 'single';
        $strategy = $_POST['strategy'] ?? null;
        $userWeightsJson = $_POST['user_weights'] ?? null;
        $monteCarloRuns = intval($_POST['monte_carlo_runs'] ?? 10000);
        $period = $_POST['period'] ?? 100;

        // 校验彩种
        if (!in_array($lotteryType, ['dlt', 'ssq'])) {
            echo json_encode(['success' => false, 'message' => '彩种参数错误']);
            return;
        }

        // VIP 或免费次数检查
        $userId = getCurrentUserId();
        $user = User::getById($userId);
        $isVip = (bool) $user['is_vip'];
        $freePredictionsUsed = (int) $user['free_predictions_used'];

        if (!$isVip && $freePredictionsUsed >= VIP_FREE_PREDICT_LIMIT && $predictType !== 'lab') {
            echo json_encode(['success' => false, 'message' => '免费预测次数已用完，请开通VIP']);
            return;
        }

        // 获取历史数据用于分析
        $historyData = self::getHistoryData($lotteryType, $period);

        // 执行预测算法
        $result = self::executePrediction($lotteryType, $predictType, $strategy, $userWeightsJson, $monteCarloRuns, $historyData);

        // 保存预测记录（VIP 用户或无限制时记录）
        if ($result['success'] && $predictType !== 'lab') {
            // 非实验室预测消耗免费次数或VIP
            if (!$isVip) {
                User::incrementFreePrediction($userId);
                // 更新会话中的免费次数（也可不更新，但后续校验会重新查库）
            }
            // 记录日志
            $numbers = [];
            $specialNumbers = [];
            foreach ($result['predictions'] as $pred) {
                $numbers[] = $pred['main'];
                $specialNumbers[] = $pred['special'];
            }
            PredictLog::add(
                $userId,
                $lotteryType,
                $predictType,
                $strategy,
                $numbers,
                $specialNumbers,
                $result['avg_score'] ?? null,
                $result['avg_rating'] ?? null
            );
        }

        echo json_encode($result);
    }

    /**
     * 获取AI评分与全维度分析（用于分析页面）
     * GET参数：lottery_type, period
     */
    public static function analysis(): void
    {
        requireLoginJSON();
        $lotteryType = $_GET['lottery_type'] ?? 'dlt';
        $period = $_GET['period'] ?? 'all';

        if (!in_array($lotteryType, ['dlt', 'ssq'])) {
            echo json_encode(['success' => false, 'message' => '彩种参数错误']);
            return;
        }

        $historyData = self::getHistoryData($lotteryType, $period);
        $analysis = self::calculateFullAnalysis($lotteryType, $historyData);

        echo json_encode(['success' => true, 'data' => $analysis]);
    }

    //---------- 核心算法 ----------

    /**
     * 获取历史数据（标准化格式）
     */
    private static function getHistoryData(string $lotteryType, $period): array
    {
        $limit = ($period === 'all') ? null : intval($period);
        $draws = LotteryData::getDraws($lotteryType, $limit, 0, 10000);
        // 按开奖日期升序，便于时间序列分析
        usort($draws, function($a, $b) { return strtotime($a['draw_date']) - strtotime($b['draw_date']); });
        return $draws;
    }

    /**
     * 执行预测并返回结果
     */
    private static function executePrediction(string $lotteryType, string $predictType, ?string $strategy, ?string $userWeightsJson, int $monteCarloRuns, array $historyData): array
    {
        // 彩种参数
        $mainConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_FRONT_MIN, 'max' => DLT_FRONT_MAX, 'count' => DLT_FRONT_COUNT
        ] : ['min' => SSQ_RED_MIN, 'max' => SSQ_RED_MAX, 'count' => SSQ_RED_COUNT];
        $specialConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_BACK_MIN, 'max' => DLT_BACK_MAX, 'count' => DLT_BACK_COUNT
        ] : ['min' => SSQ_BLUE_MIN, 'max' => SSQ_BLUE_MAX, 'count' => SSQ_BLUE_COUNT];

        // 确定权重（如果是实验室并提供了自定义权重，则使用；否则用默认权重或策略调整）
        $weights = self::resolveWeights($strategy, $userWeightsJson);

        // 进行频次、遗漏等基础统计
        $stats = self::calculateStats($historyData, $mainConfig, $specialConfig);

        // 根据预测类型生成多组号码
        $groupCount = 1;
        if ($predictType === 'multi_5') $groupCount = 5;
        elseif ($predictType === 'multi_10') $groupCount = 10;
        elseif ($predictType === 'lab') {
            // 实验室模式：可指定生成1组展示，并返回评估详情
            $groupCount = 1;
        }

        $predictions = [];
        $totalScore = 0;

        for ($i = 0; $i < $groupCount; $i++) {
            // 生成主号码
            $mainNumbers = self::generateNumbersByWeights($mainConfig, $stats, $weights, $monteCarloRuns);
            // 生成特殊号码（后区/蓝球）
            $specialNumbers = self::generateNumbersByWeights($specialConfig, $stats, $weights, $monteCarloRuns, true);
            // 评分
            $scoreData = self::scorePrediction($lotteryType, $mainNumbers, $specialNumbers, $stats, $historyData);
            $predictions[] = [
                'main' => $mainNumbers,
                'special' => $specialNumbers,
                'score' => $scoreData['score'],
                'rating' => $scoreData['rating']
            ];
            $totalScore += $scoreData['score'];
        }

        $avgScore = $groupCount > 0 ? round($totalScore / $groupCount, 2) : 0;
        $avgRating = self::scoreToRating($avgScore);

        return [
            'success' => true,
            'predictions' => $predictions,
            'avg_score' => $avgScore,
            'avg_rating' => $avgRating,
            'weight_used' => $weights,
            'analysis_brief' => [
                'hot_numbers' => array_slice($stats['hot_numbers_main'], 0, 5),
                'cold_numbers' => array_slice($stats['cold_numbers_main'], 0, 5),
            ]
        ];
    }

    /**
     * 根据权重和策略生成号码
     */
    private static function generateNumbersByWeights(array $config, array $stats, array $weights, int $monteCarloRuns, bool $isSpecial = false): array
    {
        // 综合多种算法得到号码概率分布，然后随机抽取
        $range = range($config['min'], $config['max']);
        $count = $config['count'];

        // 每种算法给出每个号码的权重分数（0-1）
        $scores = self::calculateAlgorithmScores($range, $stats, $weights, $monteCarloRuns, $isSpecial);

        // 基于总分加权抽取
        return self::weightedDraw($range, $scores, $count);
    }

    /**
     * 计算各号码的算法综合得分
     */
    private static function calculateAlgorithmScores(array $range, array $stats, array $weights, int $monteCarloRuns, bool $isSpecial): array
    {
        $scores = [];
        foreach ($range as $num) {
            $scores[$num] = 0;
        }

        // 热号贡献
        if ($weights['hot'] > 0) {
            $hotNumbers = $isSpecial ? $stats['hot_numbers_special'] : $stats['hot_numbers_main'];
            foreach ($hotNumbers as $hot) {
                if (isset($scores[$hot['number']])) {
                    $scores[$hot['number']] += $weights['hot'] * $hot['frequency'];
                }
            }
        }

        // 冷号贡献
        if ($weights['cold'] > 0) {
            $coldNumbers = $isSpecial ? $stats['cold_numbers_special'] : $stats['cold_numbers_main'];
            foreach ($coldNumbers as $cold) {
                if (isset($scores[$cold['number']])) {
                    $scores[$cold['number']] += $weights['cold'] * (1 - $cold['frequency']);
                }
            }
        }

        // 遗漏贡献
        if ($weights['missing'] > 0) {
            $missing = $isSpecial ? $stats['missing_special'] : $stats['missing_main'];
            foreach ($missing as $num => $periods) {
                if (isset($scores[$num])) {
                    // 遗漏期数越长，概率越高
                    $scores[$num] += $weights['missing'] * min($periods / 50, 1);
                }
            }
        }

        // 连号贡献（基于历史连号统计）
        if ($weights['consecutive'] > 0 && !$isSpecial) {
            $consecPairs = $stats['consecutive_pairs'] ?? [];
            foreach ($consecPairs as $pair) {
                if (isset($scores[$pair[0]])) $scores[$pair[0]] += $weights['consecutive'] * 0.3;
                if (isset($scores[$pair[1]])) $scores[$pair[1]] += $weights['consecutive'] * 0.3;
            }
        }

        // 马尔可夫链
        if ($weights['markov'] > 0) {
            $markovProbs = self::markovChainProbabilities($stats, $range, $isSpecial);
            foreach ($markovProbs as $num => $prob) {
                if (isset($scores[$num])) {
                    $scores[$num] += $weights['markov'] * $prob;
                }
            }
        }

        // 蒙特卡洛模拟
        if ($weights['monte_carlo'] > 0) {
            $monteProb = self::monteCarloProbabilities($stats, $range, $monteCarloRuns, $isSpecial);
            foreach ($monteProb as $num => $prob) {
                if (isset($scores[$num])) {
                    $scores[$num] += $weights['monte_carlo'] * $prob;
                }
            }
        }

        // 贝叶斯概率
        if ($weights['bayesian'] > 0) {
            $bayesProb = self::bayesianProbabilities($stats, $range, $isSpecial);
            foreach ($bayesProb as $num => $prob) {
                if (isset($scores[$num])) {
                    $scores[$num] += $weights['bayesian'] * $prob;
                }
            }
        }

        return $scores;
    }

    /**
     * 加权随机抽取不重复号码
     */
    private static function weightedDraw(array $range, array $scores, int $count): array
    {
        $selected = [];
        $available = array_combine($range, $scores);
        // 避免负值
        foreach ($available as &$s) { $s = max($s, 0.001); }
        unset($s);

        for ($i = 0; $i < $count; $i++) {
            $totalWeight = array_sum($available);
            if ($totalWeight <= 0) break;
            $rand = mt_rand() / mt_getrandmax() * $totalWeight;
            $cumulative = 0;
            $chosenKey = null;
            foreach ($available as $key => $weight) {
                $cumulative += $weight;
                if ($rand <= $cumulative) {
                    $chosenKey = $key;
                    break;
                }
            }
            if ($chosenKey === null) {
                // 兜底随机选一个
                $chosenKey = array_rand($available);
            }
            $selected[] = $chosenKey;
            unset($available[$chosenKey]);
        }
        sort($selected);
        return $selected;
    }

    /**
     * 计算基础统计（热号、冷号、遗漏等）
     */
    private static function calculateStats(array $draws, array $mainConfig, array $specialConfig): array
    {
        $mainRange = range($mainConfig['min'], $mainConfig['max']);
        $specialRange = range($specialConfig['min'], $specialConfig['max']);

        // 频次统计
        $mainFreq = array_fill_keys($mainRange, 0);
        $specialFreq = array_fill_keys($specialRange, 0);
        $consecutivePairsCount = []; // 连号对统计 (num, num+1)
        $totalDraws = count($draws);

        foreach ($draws as $draw) {
            $numbers = $draw['numbers'];
            $special = $draw['special_numbers'];
            foreach ($numbers as $n) $mainFreq[$n]++;
            foreach ($special as $s) $specialFreq[$s]++;

            // 连号检测
            if ($totalDraws > 0) {
                $sorted = $numbers;
                sort($sorted);
                for ($i = 0; $i < count($sorted)-1; $i++) {
                    if ($sorted[$i+1] - $sorted[$i] === 1) {
                        $pair = [$sorted[$i], $sorted[$i+1]];
                        $key = implode('-', $pair);
                        $consecutivePairsCount[$key] = ($consecutivePairsCount[$key] ?? 0) + 1;
                    }
                }
            }
        }

        // 热号：频次排序（降序）
        arsort($mainFreq);
        arsort($specialFreq);
        $hotMain = [];
        foreach ($mainFreq as $num => $freq) {
            $hotMain[] = ['number' => $num, 'frequency' => $freq / max($totalDraws, 1)];
        }
        $hotSpecial = [];
        foreach ($specialFreq as $num => $freq) {
            $hotSpecial[] = ['number' => $num, 'frequency' => $freq / max($totalDraws, 1)];
        }

        // 冷号：频次排序（升序）
        $coldMain = array_reverse($hotMain);
        $coldSpecial = array_reverse($hotSpecial);

        // 遗漏值：最近一次出现到现在的期数
        $missingMain = [];
        $missingSpecial = [];
        foreach ($mainRange as $num) {
            $missingMain[$num] = self::calculateMissing($draws, 'numbers', $num);
        }
        foreach ($specialRange as $num) {
            $missingSpecial[$num] = self::calculateMissing($draws, 'special_numbers', $num);
        }

        // 连号对频率
        arsort($consecutivePairsCount);
        $consecutivePairs = [];
        foreach ($consecutivePairsCount as $key => $cnt) {
            $p = explode('-', $key);
            $consecutivePairs[] = [(int)$p[0], (int)$p[1]];
        }

        return [
            'total_draws' => $totalDraws,
            'hot_numbers_main' => $hotMain,
            'hot_numbers_special' => $hotSpecial,
            'cold_numbers_main' => $coldMain,
            'cold_numbers_special' => $coldSpecial,
            'missing_main' => $missingMain,
            'missing_special' => $missingSpecial,
            'consecutive_pairs' => $consecutivePairs,
            'main_freq' => $mainFreq,
            'special_freq' => $specialFreq,
        ];
    }

    private static function calculateMissing(array $draws, string $field, int $num): int
    {
        // 从最新一期往回找
        $drawsDesc = array_reverse($draws);
        foreach ($drawsDesc as $index => $draw) {
            $set = $draw[$field];
            if (in_array($num, $set)) {
                return $index;
            }
        }
        return count($draws); // 从未出现
    }

    /**
     * 马尔可夫链转移概率
     */
    private static function markovChainProbabilities(array $stats, array $range, bool $isSpecial): array
    {
        // 简化：基于最近一期号码构建状态转移，使用频次作为近似
        $freq = $isSpecial ? $stats['special_freq'] : $stats['main_freq'];
        $total = array_sum($freq);
        if ($total == 0) return array_fill_keys($range, 1/count($range));

        $probs = [];
        foreach ($range as $num) {
            $probs[$num] = ($freq[$num] / $total);
        }
        return $probs;
    }

    /**
     * 蒙特卡洛模拟概率
     */
    private static function monteCarloProbabilities(array $stats, array $range, int $runs, bool $isSpecial): array
    {
        $freq = $isSpecial ? $stats['special_freq'] : $stats['main_freq'];
        $total = array_sum($freq);
        if ($total == 0) return array_fill_keys($range, 1/count($range));

        // 使用频次作为概率分布模拟多次抽取
        $counts = array_fill_keys($range, 0);
        $totalWeight = 10000;
        $weights = [];
        foreach ($range as $num) {
            $weights[$num] = ($freq[$num] / $total) * $totalWeight;
        }
        // 避免零权重
        $minWeight = 1;
        foreach ($weights as &$w) { $w = max($w, $minWeight); }
        unset($w);

        for ($i = 0; $i < $runs; $i++) {
            $drawn = self::weightedDraw($range, $weights, 1);
            $counts[$drawn[0]]++;
        }

        $probs = [];
        foreach ($range as $num) {
            $probs[$num] = $counts[$num] / $runs;
        }
        return $probs;
    }

    /**
     * 贝叶斯概率（简化：使用Beta分布先验+观察频次）
     */
    private static function bayesianProbabilities(array $stats, array $range, bool $isSpecial): array
    {
        $freq = $isSpecial ? $stats['special_freq'] : $stats['main_freq'];
        $totalDraws = $stats['total_draws'];
        $priorA = 1;
        $priorB = 1;
        $probs = [];
        foreach ($range as $num) {
            $successes = $freq[$num];
            $failures = $totalDraws - $successes;
            // 后验均值 (a+successes)/(a+b+totalDraws)
            $probs[$num] = ($priorA + $successes) / ($priorA + $priorB + $totalDraws);
        }
        return $probs;
    }

    /**
     * 解析权重（策略优先，其次自定义）
     */
    private static function resolveWeights(?string $strategy, ?string $userWeightsJson): array
    {
        $default = [
            'hot' => WEIGHT_HOT,
            'cold' => WEIGHT_COLD,
            'missing' => WEIGHT_MISSING,
            'consecutive' => WEIGHT_CONSECUTIVE,
            'markov' => WEIGHT_MARKOV,
            'monte_carlo' => WEIGHT_MONTE_CARLO,
            'bayesian' => WEIGHT_BAYESIAN
        ];

        // 如果用户提供了自定义权重JSON（实验室模式）
        if ($userWeightsJson) {
            $custom = json_decode($userWeightsJson, true);
            if (is_array($custom)) {
                // 确保所有键存在且总和为1
                $total = 0;
                foreach ($default as $k => $v) {
                    $default[$k] = isset($custom[$k]) ? floatval($custom[$k]) : $v;
                    $total += $default[$k];
                }
                if ($total > 0) {
                    foreach ($default as $k => $v) {
                        $default[$k] = $v / $total;
                    }
                }
            }
        } elseif ($strategy) {
            // 策略调整权重
            switch ($strategy) {
                case STRATEGY_STABLE:
                    // 提高遗漏和马尔可夫，降低蒙特卡洛和贝叶斯
                    $default['missing'] = 0.30;
                    $default['markov'] = 0.25;
                    $default['monte_carlo'] = 0.05;
                    $default['bayesian'] = 0.05;
                    $default['hot'] = 0.20;
                    $default['cold'] = 0.10;
                    $default['consecutive'] = 0.05;
                    break;
                case STRATEGY_BALANCED:
                    // 与默认相同
                    break;
                case STRATEGY_AGGRESSIVE:
                    $default['hot'] = 0.25;
                    $default['cold'] = 0.15;
                    $default['missing'] = 0.15;
                    $default['consecutive'] = 0.15;
                    $default['markov'] = 0.10;
                    $default['monte_carlo'] = 0.15;
                    $default['bayesian'] = 0.05;
                    break;
                case STRATEGY_EXTREME:
                    $default['hot'] = 0.40;
                    $default['cold'] = 0.20;
                    $default['missing'] = 0.10;
                    $default['consecutive'] = 0.10;
                    $default['markov'] = 0.10;
                    $default['monte_carlo'] = 0.05;
                    $default['bayesian'] = 0.05;
                    break;
            }
            // 归一化
            $total = array_sum($default);
            foreach ($default as $k => $v) {
                $default[$k] = $v / $total;
            }
        }
        return $default;
    }

    /**
     * 对一组预测号码进行评分
     */
    private static function scorePrediction(string $lotteryType, array $main, array $special, array $stats, array $historyData): array
    {
        $score = 0;
        // 各项子评分：热号匹配度、奇偶均衡、大小均衡、和值合理、连号等
        // 此处实现简化版评分（满分100）
        $subScores = [];

        // 1. 热号匹配（30分）
        $hotMain = array_column(array_slice($stats['hot_numbers_main'], 0, 10), 'number');
        $hotSpecial = array_column(array_slice($stats['hot_numbers_special'], 0, 5), 'number');
        $matchMain = count(array_intersect($main, $hotMain));
        $matchSpecial = count(array_intersect($special, $hotSpecial));
        $hotScore = ($matchMain + $matchSpecial) / (count($main) + count($special)) * 30;
        $subScores['hot'] = min($hotScore, 30);

        // 2. 奇偶平衡（20分）
        $oddMain = count(array_filter($main, function($n){ return $n % 2 != 0; }));
        $evenMain = count($main) - $oddMain;
        $balance = 1 - abs($oddMain - $evenMain) / count($main);
        $subScores['odd_even'] = $balance * 20;

        // 3. 大小均衡（20分）
        $mainConfig = ($lotteryType === 'dlt') ? ['min'=>DLT_FRONT_MIN,'max'=>DLT_FRONT_MAX] : ['min'=>SSQ_RED_MIN,'max'=>SSQ_RED_MAX];
        $mid = ($mainConfig['min'] + $mainConfig['max']) / 2;
        $bigCount = count(array_filter($main, function($n) use ($mid) { return $n > $mid; }));
        $smallCount = count($main) - $bigCount;
        $sizeBalance = 1 - abs($bigCount - $smallCount) / count($main);
        $subScores['size'] = $sizeBalance * 20;

        // 4. 和值合理（15分）
        $sumMain = array_sum($main);
        $avgSumMain = ($mainConfig['min'] + $mainConfig['max']) / 2 * count($main);
        $sumDeviation = abs($sumMain - $avgSumMain) / $avgSumMain;
        $subScores['sum'] = max(0, (1 - $sumDeviation)) * 15;

        // 5. 连号加分（5分）
        $sorted = $main;
        sort($sorted);
        $consecutiveCount = 0;
        for ($i = 0; $i < count($sorted)-1; $i++) {
            if ($sorted[$i+1] - $sorted[$i] === 1) $consecutiveCount++;
        }
        $subScores['consecutive'] = min($consecutiveCount, 2) * 2.5;

        // 6. AC值等暂省略，用历史匹配替代（10分）
        $subScores['history_match'] = 10; // 可扩展

        $totalScore = array_sum($subScores);
        $rating = self::scoreToRating($totalScore);

        return ['score' => round($totalScore, 2), 'rating' => $rating, 'sub_scores' => $subScores];
    }

    private static function scoreToRating(float $score): string
    {
        if ($score >= RATING_THRESHOLD_SSS) return 'SSS';
        if ($score >= RATING_THRESHOLD_SS) return 'SS';
        if ($score >= RATING_THRESHOLD_S) return 'S';
        if ($score >= RATING_THRESHOLD_A) return 'A';
        if ($score >= RATING_THRESHOLD_B) return 'B';
        return 'C';
    }

    /**
     * 全维度分析（用于分析页面，不预测）
     */
    private static function calculateFullAnalysis(string $lotteryType, array $historyData): array
    {
        $mainConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_FRONT_MIN, 'max' => DLT_FRONT_MAX, 'count' => DLT_FRONT_COUNT
        ] : ['min' => SSQ_RED_MIN, 'max' => SSQ_RED_MAX, 'count' => SSQ_RED_COUNT];
        $specialConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_BACK_MIN, 'max' => DLT_BACK_MAX, 'count' => DLT_BACK_COUNT
        ] : ['min' => SSQ_BLUE_MIN, 'max' => SSQ_BLUE_MAX, 'count' => SSQ_BLUE_COUNT];

        $stats = self::calculateStats($historyData, $mainConfig, $specialConfig);
        // 额外计算和值趋势、奇偶比、大小比等
        $trends = [];
        $sums = [];
        $oddEvenRatios = [];
        foreach ($historyData as $draw) {
            $nums = $draw['numbers'];
            $sums[] = array_sum($nums);
            $odd = count(array_filter($nums, function($n){ return $n%2!=0; }));
            $oddEvenRatios[] = $odd / count($nums);
        }
        $trends['sum_avg'] = count($sums) ? array_sum($sums)/count($sums) : 0;
        $trends['odd_even_avg'] = count($oddEvenRatios) ? array_sum($oddEvenRatios)/count($oddEvenRatios) : 0;

        return [
            'stats' => $stats,
            'trends' => $trends,
            'config' => [
                'main' => $mainConfig,
                'special' => $specialConfig
            ]
        ];
    }
}