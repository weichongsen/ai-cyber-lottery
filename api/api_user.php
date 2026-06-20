<?php
/**
 * 用户相关 AJAX 接口
 * 
 * 路由分发：/api/api_user.php?action=xxx
 * 前端 ajax_request.js 调用此接口。
 */

// 初始化环境（加载配置、会话、模型等）
require_once __DIR__ . '/../config/session_init.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../controller/AuthCtrl.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        AuthCtrl::login();
        break;

    case 'register':
        AuthCtrl::register();
        break;

    case 'logout':
        // 登出是页面跳转，但在AJAX中也可以返回状态
        if (isLoggedIn()) {
            logoutUser();
            echo json_encode(['success' => true, 'message' => '已登出']);
        } else {
            echo json_encode(['success' => false, 'message' => '未登录']);
        }
        break;

    case 'session':
        AuthCtrl::getSessionUser();
        break;

    case 'profile':
        // 获取当前用户信息
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => '未登录']);
            break;
        }
        $user = User::getById(getCurrentUserId());
        echo json_encode(['success' => true, 'data' => $user]);
        break;

    case 'update_profile':
        // 更新用户名或邮箱 (POST)
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => '未登录']);
            break;
        }
        $data = [];
        if (isset($_POST['username'])) $data['username'] = trim($_POST['username']);
        if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
        $result = User::updateProfile(getCurrentUserId(), $data);
        echo json_encode(['success' => $result, 'message' => $result ? '更新成功' : '无变更或失败']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
}