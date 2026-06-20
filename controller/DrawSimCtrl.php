<?php
/**
 * 摇奖模拟控制器
 */

require_once __DIR__ . '/../config/db_supabase.php';
require_once __DIR__ . '/../config/constant.php';
require_once __DIR__ . '/../model/LotteryData.php';

class DrawSimCtrl
{
    /**
     * 模拟摇奖：返回一组随机号码（但依据历史数据加权，更具真实感）
     * GET参数：lottery_type = dlt|ssq
     */
    public static function simulate(): void
    {
        $lotteryType = $_GET['lottery_type'] ?? 'dlt';
        if (!in_array($lotteryType, ['dlt', 'ssq'])) {
            echo json_encode(['success' => false, 'message' => '彩种参数错误']);
            return;
        }

        // 获取历史数据用于加权
        $draws = LotteryData::getDraws($lotteryType, 100); // 最近100期
        $mainConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_FRONT_MIN, 'max' => DLT_FRONT_MAX, 'count' => DLT_FRONT_COUNT
        ] : ['min' => SSQ_RED_MIN, 'max' => SSQ_RED_MAX, 'count' => SSQ_RED_COUNT];
        $specialConfig = ($lotteryType === 'dlt') ? [
            'min' => DLT_BACK_MIN, 'max' => DLT_BACK_MAX, 'count' => DLT_BACK_COUNT
        ] : ['min' => SSQ_BLUE_MIN, 'max' => SSQ_BLUE_MAX, 'count' => SSQ_BLUE_COUNT];

        // 统计频次
        $mainFreq = array_fill_keys(range($mainConfig['min'], $mainConfig['max']), 0);
        $specialFreq = array_fill_keys(range($specialConfig['min'], $specialConfig['max']), 0);
        foreach ($draws as $draw) {
            foreach ($draw['numbers'] as $n) $mainFreq[$n]++;
            foreach ($draw['special_numbers'] as $s) $specialFreq[$s]++;
        }

        // 加权抽取
        $mainNumbers = self::weightedRandomSelect($mainFreq, $mainConfig['count']);
        $specialNumbers = self::weightedRandomSelect($specialFreq, $specialConfig['count']);

        // 构建单个球序列，前端按顺序展示动画
        $ballSequence = array_merge(
            array_map(function($n){ return ['type'=>'main','number'=>$n]; }, $mainNumbers),
            array_map(function($n){ return ['type'=>'special','number'=>$n]; }, $specialNumbers)
        );

        echo json_encode([
            'success' => true,
            'lottery_type' => $lotteryType,
            'main' => $mainNumbers,
            'special' => $specialNumbers,
            'ball_sequence' => $ballSequence
        ]);
    }

    /**
     * 加权随机选择不重复号码
     */
    private static function weightedRandomSelect(array $frequency, int $count): array
    {
        $weights = $frequency;
        // 确保最小权重1
        foreach ($weights as &$w) { $w = max($w, 1); }
        unset($w);
        $selected = [];
        $available = $weights;

        for ($i = 0; $i < $count; $i++) {
            $total = array_sum($available);
            if ($total <= 0) break;
            $rand = mt_rand() / mt_getrandmax() * $total;
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
                $chosenKey = array_rand($available);
            }
            $selected[] = $chosenKey;
            unset($available[$chosenKey]);
        }
        sort($selected);
        return $selected;
    }
}