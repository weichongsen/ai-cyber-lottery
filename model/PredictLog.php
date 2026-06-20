<?php
/**
 * 预测记录模型 - 存储和查询AI预测历史
 */

require_once __DIR__ . '/../config/db_supabase.php';

class PredictLog
{
    /**
     * 记录预测结果
     *
     * @param int $userId
     * @param string $lotteryType
     * @param string $predictType 'single', 'multi_5', 'multi_10', 'lab'
     * @param string|null $strategy 策略类型（实验室使用）
     * @param array $numbers 二维数组（多组预测）或一维数组（单组）
     * @param array $specialNumbers 对应特殊号码
     * @param float|null $aiScore
     * @param string|null $rating
     * @return int 插入的记录ID
     */
    public static function add(int $userId, string $lotteryType, string $predictType, ?string $strategy, array $numbers, array $specialNumbers, ?float $aiScore = null, ?string $rating = null): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO predict_logs (user_id, lottery_type, predict_type, strategy, numbers, special_numbers, ai_score, rating) VALUES (?, ?, ?, ?, ?::jsonb, ?::jsonb, ?, ?)");
        $stmt->execute([$userId, $lotteryType, $predictType, $strategy, json_encode($numbers), json_encode($specialNumbers), $aiScore, $rating]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * 获取用户预测历史
     *
     * @param int $userId
     * @param string|null $lotteryType
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getByUser(int $userId, ?string $lotteryType = null, int $limit = 30, int $offset = 0): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM predict_logs WHERE user_id = ?";
        $params = [$userId];
        if ($lotteryType) {
            $sql .= " AND lottery_type = ?";
            $params[] = $lotteryType;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['numbers'] = json_decode($row['numbers'], true) ?? [];
            $row['special_numbers'] = json_decode($row['special_numbers'], true) ?? [];
        }
        return $rows;
    }

    /**
     * 批量删除预测记录
     *
     * @param int $userId
     * @param array $ids
     * @return int
     */
    public static function deleteBatch(int $userId, array $ids): int
    {
        if (empty($ids)) return 0;
        $pdo = getDBConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM predict_logs WHERE id IN ($placeholders) AND user_id = ?");
        $params = $ids;
        $params[] = $userId;
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 清空用户预测历史
     *
     * @param int $userId
     * @return void
     */
    public static function clearByUser(int $userId): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM predict_logs WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}