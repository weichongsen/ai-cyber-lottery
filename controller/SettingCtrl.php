<?php
/**
 * 系统设置控制器
 */

require_once __DIR__ . '/../model/SystemConfig.php';
require_once __DIR__ . '/../config/session_init.php';

class SettingCtrl
{
    /**
     * 获取当前用户的所有设置
     */
    public static function getSettings(): void
    {
        requireLoginJSON();
        $configs = SystemConfig::getAllByUser(getCurrentUserId());
        echo json_encode(['success' => true, 'data' => $configs]);
    }

    /**
     * 保存设置 (POST)
     * 参数: settings (JSON对象)
     */
    public static function saveSettings(): void
    {
        requireLoginJSON();
        $settings = json_decode($_POST['settings'] ?? '{}', true);
        if (empty($settings)) {
            echo json_encode(['success' => false, 'message' => '设置数据为空']);
            return;
        }
        SystemConfig::setBatch(getCurrentUserId(), $settings);
        echo json_encode(['success' => true, 'message' => '设置已保存']);
    }

    /**
     * 重置设置
     */
    public static function resetSettings(): void
    {
        requireLoginJSON();
        SystemConfig::reset(getCurrentUserId());
        echo json_encode(['success' => true, 'message' => '设置已重置']);
    }
}