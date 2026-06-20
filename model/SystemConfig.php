<?php
/**
 * 系统配置模型 - 存储用户自定义设置（主题、开关等）
 */

require_once __DIR__ . '/../config/db_supabase.php';

class SystemConfig
{
    /**
     * 获取用户的所有配置项
     *
     * @param int $userId
     * @return array 关联数组 key => value
     */
    public static function getAllByUser(int $userId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT config_key, config_value FROM system_config WHERE user_id = ?");
        $stmt->execute([$userId]);
        $configs = [];
        while ($row = $stmt->fetch()) {
            $configs[$row['config_key']] = $row['config_value'];
        }
        return $configs;
    }

    /**
     * 设置或更新单个配置项
     *
     * @param int $userId
     * @param string $key
     * @param string $value
     * @return void
     */
    public static function set(int $userId, string $key, string $value): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO system_config (user_id, config_key, config_value) VALUES (?, ?, ?) ON CONFLICT (user_id, config_key) DO UPDATE SET config_value = EXCLUDED.config_value, updated_at = NOW()");
        $stmt->execute([$userId, $key, $value]);
    }

    /**
     * 批量设置配置
     *
     * @param int $userId
     * @param array $configs 键值对数组
     */
    public static function setBatch(int $userId, array $configs): void
    {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            foreach ($configs as $key => $value) {
                self::set($userId, $key, $value);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * 删除用户的所有配置（重置设置）
     *
     * @param int $userId
     * @return void
     */
    public static function reset(int $userId): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM system_config WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}