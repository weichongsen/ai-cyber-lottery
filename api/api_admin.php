<?php
/**
 * 管理员后台 AJAX 接口
 */

require_once __DIR__ . '/../config/session_init.php';
require_once __DIR__ . '/../controller/AdminCtrl.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'dashboard_stats':
        AdminCtrl::dashboard();
        break;

    case 'list_users':
        AdminCtrl::listUsers();
        break;

    case 'toggle_vip':
        AdminCtrl::toggleVip();
        break;

    case 'add_draw':
        AdminCtrl::addDraw();
        break;

    case 'update_draw':
        AdminCtrl::updateDraw();
        break;

    case 'delete_draw':
        AdminCtrl::deleteDraw();
        break;

    case 'bulk_import_draws':
        AdminCtrl::bulkImportDraws();
        break;

    case 'export_all':
        AdminCtrl::exportAll();
        break;

    case 'import_all':
        AdminCtrl::importAll();
        break;

    case 'list_audio':
        AdminCtrl::listAudio();
        break;

    case 'upload_audio':
        AdminCtrl::uploadAudio();
        break;

    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
}