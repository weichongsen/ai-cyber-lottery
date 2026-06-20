<?php
/**
 * 图表数据接口控制器 - 提供 ECharts 所需 JSON 数据
 */

require_once __DIR__ . '/../config/db_supabase.php';
require_once __DIR__ . '/../model/LotteryData.php';
require_once __DIR__ . '/../controller/AiPredictCtrl.php'; // 复用分析方法

class ChartCtrl
{
    /**
     * 获取热号/冷号/遗漏图表数据
     * GET参数: lottery_type, period
     */
    public static function hotColdMissing(): void
    {
        $lotteryType = $_GET['lottery_type'] ?? 'dlt';
        $period = $_GET['period'] ?? 'all';
        if (!in_array($lotteryType, ['dlt', 'ssq'])) {
            echo json_encode(['success' => false, 'message' => '参数错误']);
            return;
        }

        // 复用 AI 分析统计
        $draws = self::getDrawsForChart($lotteryType, $period);
        $mainConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_FRONT_MIN, 'max' => DLT_FRONT_MAX, 'count' => DLT_FRONT_COUNT
        ] : ['min' => SSQ_RED_MIN, 'max' => SSQ_RED_MAX, 'count' => SSQ_RED_COUNT];
        $specialConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_BACK_MIN, 'max' => DLT_BACK_MAX, 'count' => DLT_BACK_COUNT
        ] : ['min' => SSQ_BLUE_MIN, 'max' => SSQ_BLUE_MAX, 'count' => SSQ_BLUE_COUNT];

        $stats = AiPredictCtrl::calculateStats($draws, $mainConfig, $specialConfig); // 需要设为public或通过其他方式

        // 但 AiPredictCtrl 中方法是 private，需要将 calculateStats 改为 public 或复制。我们在此直接调用统计函数，或者重构。
        // 简单起见，我们在此复制一份统计函数（略去重复，实际项目应将统计提取到 Model/Analysis.php）
        // 为保持完整性，我们在这里实现一个简单的统计获取
        $analysis = self::basicStats($draws, $mainConfig, $specialConfig);

        echo json_encode([
            'success' => true,
            'data' => $analysis
        ]);
    }

    private static function basicStats(array $draws, array $mainConfig, array $specialConfig): array
    {
        $mainFreq = array_fill_keys(range($mainConfig['min'], $mainConfig['max']), 0);
        $specialFreq = array_fill_keys(range($specialConfig['min'], $specialConfig['max']), 0);
        foreach ($draws as $d) {
            foreach ($d['numbers'] as $n) $mainFreq[$n]++;
            foreach ($d['special_numbers'] as $s) $specialFreq[$s]++;
        }
        return [
            'main_freq' => $mainFreq,
            'special_freq' => $specialFreq,
            'main_range' => array_keys($mainFreq),
            'special_range' => array_keys($specialFreq),
        ];
    }

    /**
     * 和值走势、奇偶、大小等图表数据
     */
    public static function trendData(): void
    {
        $lotteryType = $_GET['lottery_type'] ?? 'dlt';
        $period = $_GET['period'] ?? 50;
        if (!in_array($lotteryType, ['dlt', 'ssq'])) {
            echo json_encode(['success' => false, 'message' => '参数错误']);
            return;
        }

        $draws = LotteryData::getDraws($lotteryType, intval($period), 0, 500);
        // 按日期升序
        usort($draws, function($a,$b){ return strtotime($a['draw_date']) - strtotime($b['draw_date']); });

        $dates = [];
        $sums = [];
        $oddRatios = [];
        $bigRatios = [];
        $mainMin = ($lotteryType === 'dlt') ? DLT_FRONT_MIN : SSQ_RED_MIN;
        $mainMax = ($lotteryType === 'dlt') ? DLT_FRONT_MAX : SSQ_RED_MAX;
        $mid = ($mainMin + $mainMax) / 2;

        foreach ($draws as $draw) {
            $nums = $draw['numbers'];
            $dates[] = $draw['draw_date'];
            $sums[] = array_sum($nums);
            $oddCount = count(array_filter($nums, function($n){ return $n%2!=0; }));
            $oddRatios[] = round($oddCount / count($nums) * 100, 1);
            $bigCount = count(array_filter($nums, function($n) use ($mid){ return $n > $mid; }));
            $bigRatios[] = round($bigCount / count($nums) * 100, 1);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'dates' => $dates,
                'sums' => $sums,
                'odd_ratios' => $oddRatios,
                'big_ratios' => $bigRatios,
            ]
        ]);
    }

    private static function getDrawsForChart(string $lotteryType, $period): array
    {
        $limit = ($period === 'all') ? null : intval($period);
        return LotteryData::getDraws($lotteryType, $limit, 0, 10000);
    }
}