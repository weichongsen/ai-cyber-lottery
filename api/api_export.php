<?php
/**
 * 数据导出/导入接口 (可被普通用户调用, 权限在控制器内校验)
 * 这里主要提供用户个人数据导出
 */

require_once __DIR__ . '/../config/session_init.php';
require_once __DIR__ . '/../controller/CollectCtrl.php';
require_once __DIR__ . '/../controller/SettingCtrl.php';
require_once __DIR__ . '/../controller/VipCtrl.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add_collect':
        CollectCtrl::addCollect();
        break;

    case 'list_collect':
        CollectCtrl::listCollect();
        break;

    case 'delete_collect':
        CollectCtrl::deleteCollect();
        break;

    case 'list_predict_history':
        CollectCtrl::listPredictHistory();
        break;

    case 'delete_predict_history':
        CollectCtrl::deletePredictHistory();
        break;

    case 'clear_predict_history':
        CollectCtrl::clearPredictHistory();
        break;

    case 'get_settings':
        SettingCtrl::getSettings();
        break;

    case 'save_settings':
        SettingCtrl::saveSettings();
        break;

    case 'reset_settings':
        SettingCtrl::resetSettings();
        break;

    case 'check_vip':
        VipCtrl::checkStatus();
        break;

    case 'activate_vip':
        VipCtrl::activate();
        break;

    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
}