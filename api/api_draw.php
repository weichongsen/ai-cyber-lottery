<?php
/**
 * 摇奖模拟 AJAX 接口
 */

require_once __DIR__ . '/../config/session_init.php';
require_once __DIR__ . '/../controller/DrawSimCtrl.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'simulate':
        // 执行模拟摇奖 (GET)
        DrawSimCtrl::simulate();
        break;

    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
}