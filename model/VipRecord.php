<?php
/**
 * VIP操作记录模型
 */

require_once __DIR__ . '/../config/db_supabase.php';

class VipRecord
{
    /**
     * 添加一条VIP操作记录
     *
     * @param int $userId 目标用户
     * @param string $action 'activate' 或 'cancel'
     * @param int $operatedBy 操作管理员ID
     * @param string|null $reason 操作原因
     * @return int 记录ID
     */
    public static function add(int $userId, string $action, int $operatedBy, ?string $reason = null): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO vip_records (user_id, action, operated_by, reason) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $operatedBy, $reason]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * 获取某个用户的VIP操作历史
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public static function getByUser(int $userId, int $limit = 20): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT vr.*, u.username AS operator_name FROM vip_records vr LEFT JOIN users u ON vr.operated_by = u.id WHERE vr.user_id = ? ORDER BY vr.created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * 获取全部VIP记录（管理员查看）
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getAll(int $limit = 100, int $offset = 0): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT vr.*, u.username AS target_username, op.username AS operator_username FROM vip_records vr JOIN users u ON vr.user_id = u.id JOIN users op ON vr.operated_by = op.id ORDER BY vr.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}