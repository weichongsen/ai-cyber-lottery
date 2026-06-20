<?php
/**
 * 收藏模型 - 号码收藏的新增、查询、删除
 */

require_once __DIR__ . '/../config/db_supabase.php';

class Collect
{
    /**
     * 添加收藏
     *
     * @param int $userId
     * @param string $lotteryType 'dlt' 或 'ssq'
     * @param array $numbers
     * @param array $specialNumbers
     * @param string|null $note 备注
     * @return int 收藏ID
     */
    public static function add(int $userId, string $lotteryType, array $numbers, array $specialNumbers, ?string $note = null): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO collections (user_id, lottery_type, numbers, special_numbers, note) VALUES (?, ?, ?::jsonb, ?::jsonb, ?)");
        $stmt->execute([$userId, $lotteryType, json_encode($numbers), json_encode($specialNumbers), $note]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * 获取用户收藏列表（支持彩种筛选、搜索号码）
     *
     * @param int $userId
     * @param string|null $lotteryType 可选过滤
     * @param string|null $searchKeyword 搜索号码（逗号分隔）
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getByUser(int $userId, ?string $lotteryType = null, ?string $searchKeyword = null, int $limit = 50, int $offset = 0): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM collections WHERE user_id = ?";
        $params = [$userId];

        if ($lotteryType) {
            $sql .= " AND lottery_type = ?";
            $params[] = $lotteryType;
        }

        if ($searchKeyword) {
            // 简单搜索：号码文本匹配 JSON 数组的字符串表示
            $sql .= " AND (CAST(numbers AS TEXT) LIKE ? OR CAST(special_numbers AS TEXT) LIKE ?)";
            $likeKeyword = '%' . $searchKeyword . '%';
            $params[] = $likeKeyword;
            $params[] = $likeKeyword;
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
     * 批量删除收藏
     *
     * @param int $userId
     * @param array $ids 收藏ID数组
     * @return int 删除条数
     */
    public static function deleteBatch(int $userId, array $ids): int
    {
        if (empty($ids)) return 0;
        $pdo = getDBConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM collections WHERE id IN ($placeholders) AND user_id = ?");
        $params = $ids;
        $params[] = $userId;
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 获取收藏总数（用于分页）
     *
     * @param int $userId
     * @param string|null $lotteryType
     * @return int
     */
    public static function countByUser(int $userId, ?string $lotteryType = null): int
    {
        $pdo = getDBConnection();
        $sql = "SELECT COUNT(*) FROM collections WHERE user_id = ?";
        $params = [$userId];
        if ($lotteryType) {
            $sql .= " AND lottery_type = ?";
            $params[] = $lotteryType;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}