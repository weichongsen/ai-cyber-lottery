<?php
/**
 * 管理员专用模型 - 管理员功能相关的数据库操作
 */

require_once __DIR__ . '/../config/db_supabase.php';

class Admin
{
    /**
     * 验证用户是否为管理员
     *
     * @param int $userId
     * @return bool
     */
    public static function checkIsAdmin(int $userId): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        return $user && $user['role'] === ROLE_ADMIN;
    }

    /**
     * 获取仪表盘统计数据
     *
     * @return array
     */
    public static function getDashboardStats(): array
    {
        $pdo = getDBConnection();
        $stats = [];

        // 用户总数
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = (int) $stmt->fetchColumn();

        // VIP用户数
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_vip = TRUE");
        $stats['vip_users'] = (int) $stmt->fetchColumn();

        // 大乐透开奖数
        $stmt = $pdo->query("SELECT COUNT(*) FROM lottery_draws WHERE lottery_type = 'dlt'");
        $stats['dlt_draws'] = (int) $stmt->fetchColumn();

        // 双色球开奖数
        $stmt = $pdo->query("SELECT COUNT(*) FROM lottery_draws WHERE lottery_type = 'ssq'");
        $stats['ssq_draws'] = (int) $stmt->fetchColumn();

        // 总预测次数
        $stmt = $pdo->query("SELECT COUNT(*) FROM predict_logs");
        $stats['total_predictions'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    /**
     * 导出所有数据（用于备份），返回 JSON 结构
     *
     * @return array
     */
    public static function exportAllData(): array
    {
        $pdo = getDBConnection();
        $data = [];

        $tables = ['users', 'lottery_draws', 'collections', 'predict_logs', 'vip_records', 'system_config', 'audio_files'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT * FROM $table");
            $data[$table] = $stmt->fetchAll();
            // 解析 JSONB 字段
            foreach ($data[$table] as &$row) {
                foreach (['numbers', 'special_numbers'] as $key) {
                    if (isset($row[$key]) && is_string($row[$key])) {
                        $decoded = json_decode($row[$key], true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $row[$key] = $decoded;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 导入数据（覆盖现有表，谨慎使用）
     *
     * @param array $data 格式与 exportAllData 相同
     * @return bool
     */
    public static function importAllData(array $data): bool
    {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            foreach ($data as $table => $rows) {
                if (empty($rows)) continue;
                // 简单清空并重新插入，生产环境需考虑依赖顺序
                $pdo->exec("TRUNCATE TABLE $table CASCADE");
                foreach ($rows as $row) {
                    $columns = array_keys($row);
                    $placeholders = array_fill(0, count($columns), '?');
                    $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $values = array_values($row);
                    // 对 JSONB 字段重新编码
                    foreach ($columns as $i => $col) {
                        if (in_array($col, ['numbers', 'special_numbers']) && is_array($values[$i])) {
                            $values[$i] = json_encode($values[$i]);
                        }
                    }
                    $stmt->execute($values);
                }
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Import failed: ' . $e->getMessage());
            return false;
        }
    }
}