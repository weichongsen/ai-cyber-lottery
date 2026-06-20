<?php
/**
 * 开奖数据模型 - 数据库查询、插入、更新、删除开奖记录
 */

require_once __DIR__ . '/../config/db_supabase.php';
require_once __DIR__ . '/../config/constant.php';

class LotteryData
{
    /**
     * 获取开奖记录（支持分页和筛选）
     *
     * @param string $lotteryType 'dlt' 或 'ssq'
     * @param int|null $limit 最近多少期（null 为全部）
     * @param int $offset 分页偏移
     * @param int $pageSize 分页大小，默认100
     * @return array
     */
    public static function getDraws(string $lotteryType, ?int $limit = null, int $offset = 0, int $pageSize = 100): array
    {
        $pdo = getDBConnection();

        $sql = "SELECT id, lottery_type, draw_num, draw_date, numbers, special_numbers, created_at FROM lottery_draws WHERE lottery_type = ? ORDER BY draw_date DESC, id DESC";
        $params = [$lotteryType];

        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        } else {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $pageSize;
            $params[] = $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // 解析 JSONB 字段为数组
        foreach ($rows as &$row) {
            $row['numbers'] = json_decode($row['numbers'], true) ?? [];
            $row['special_numbers'] = json_decode($row['special_numbers'], true) ?? [];
        }
        return $rows;
    }

    /**
     * 获取指定彩种的总期数
     *
     * @param string $lotteryType
     * @return int
     */
    public static function getTotalDraws(string $lotteryType): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM lottery_draws WHERE lottery_type = ?");
        $stmt->execute([$lotteryType]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * 插入一条开奖记录
     *
     * @param string $lotteryType
     * @param string $drawNum 期号
     * @param string $drawDate 开奖日期 Y-m-d
     * @param array $numbers 前区/红球
     * @param array $specialNumbers 后区/蓝球
     * @return bool
     */
    public static function insertDraw(string $lotteryType, string $drawNum, string $drawDate, array $numbers, array $specialNumbers): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO lottery_draws (lottery_type, draw_num, draw_date, numbers, special_numbers) VALUES (?, ?, ?, ?::jsonb, ?::jsonb) ON CONFLICT (lottery_type, draw_num) DO NOTHING");
        return $stmt->execute([
            $lotteryType,
            $drawNum,
            $drawDate,
            json_encode($numbers),
            json_encode($specialNumbers)
        ]);
    }

    /**
     * 更新一条开奖记录
     *
     * @param int $id
     * @param array $data 可更新字段 draw_num, draw_date, numbers, special_numbers
     * @return bool
     */
    public static function updateDraw(int $id, array $data): bool
    {
        $pdo = getDBConnection();
        $updates = [];
        $params = [];
        foreach (['draw_num', 'draw_date'] as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        foreach (['numbers', 'special_numbers'] as $jsonField) {
            if (isset($data[$jsonField]) && is_array($data[$jsonField])) {
                $updates[] = "$jsonField = ?::jsonb";
                $params[] = json_encode($data[$jsonField]);
            }
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $id;
        $sql = "UPDATE lottery_draws SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * 删除一条开奖记录
     *
     * @param int $id
     * @return bool
     */
    public static function deleteDraw(int $id): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM lottery_draws WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * 批量导入开奖记录（用于管理员对接外部API或文件）
     *
     * @param array $draws 每条记录包含 lottery_type, draw_num, draw_date, numbers, special_numbers
     * @return int 成功导入条数
     */
    public static function bulkInsert(array $draws): int
    {
        $pdo = getDBConnection();
        $successCount = 0;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO lottery_draws (lottery_type, draw_num, draw_date, numbers, special_numbers) VALUES (?, ?, ?, ?::jsonb, ?::jsonb) ON CONFLICT (lottery_type, draw_num) DO NOTHING");
            foreach ($draws as $d) {
                $stmt->execute([
                    $d['lottery_type'],
                    $d['draw_num'],
                    $d['draw_date'],
                    json_encode($d['numbers']),
                    json_encode($d['special_numbers'])
                ]);
                if ($stmt->rowCount() > 0) {
                    $successCount++;
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $successCount;
    }
}