<?php
/**
 * AI 彩票预测系统 - 统一入口 & 路由分发
 */

// 加载全局配置、会话与权限函数
require_once __DIR__ . '/config/session_init.php';

// 加载控制器（页面渲染用）
require_once __DIR__ . '/controller/AuthCtrl.php';
require_once __DIR__ . '/controller/IndexCtrl.php';
require_once __DIR__ . '/controller/PreviewCtrl.php';

// 获取请求的页面标识
$page = $_GET['page'] ?? 'dashboard';

// 需要登录才能访问的页面
$authPages = ['ai_lab', 'collect', 'predict_history', 'system_setting'];
// 管理员专属页面
$adminPages = ['admin_dash', 'user_manage', 'lottery_data_mgr', 'audio_mgr', 'data_export'];

// 权限拦截
if (in_array($page, $authPages) || in_array($page, $adminPages)) {
    if (!isLoggedIn()) {
        header('Location: /?page=login');
        exit;
    }
}
if (in_array($page, $adminPages)) {
    if (!isAdmin()) {
        header('Location: /?page=dashboard');
        exit;
    }
}

// 路由分发
switch ($page) {
    // 认证页面
    case 'login':
        IndexCtrl::loginPage();
        break;
    case 'register':
        IndexCtrl::registerPage();
        break;

    // 普通用户前台页面
    case 'dashboard':
        IndexCtrl::dashboard();
        break;
    case 'dlt':
        IndexCtrl::dlt();
        break;
    case 'ssq':
        IndexCtrl::ssq();
        break;
    case 'ai_lab':
        IndexCtrl::aiLab();
        break;
    case 'chart_view':
        IndexCtrl::chartView();
        break;
    case 'draw_sim':
        IndexCtrl::drawSim();
        break;
    case 'collect':
        IndexCtrl::collect();
        break;
    case 'predict_history':
        IndexCtrl::predictHistory();
        break;
    case 'system_setting':
        IndexCtrl::systemSetting();
        break;

    // 全屏预览
    case 'full_preview':
        PreviewCtrl::fullPreview();
        break;

    // 管理员后台页面（直接渲染视图）
    case 'admin_dash':
        $pageTitle = '管理员控制台';
        require_once __DIR__ . '/view/public/header.php';
        require_once __DIR__ . '/view/admin/admin_dash.php';
        require_once __DIR__ . '/view/public/footer.php';
        break;
    case 'user_manage':
        $pageTitle = '用户管理';
        require_once __DIR__ . '/view/public/header.php';
        require_once __DIR__ . '/view/admin/user_manage.php';
        require_once __DIR__ . '/view/public/footer.php';
        break;
    case 'lottery_data_mgr':
        $pageTitle = '开奖数据管理';
        require_once __DIR__ . '/view/public/header.php';
        require_once __DIR__ . '/view/admin/lottery_data_mgr.php';
        require_once __DIR__ . '/view/public/footer.php';
        break;
    case 'audio_mgr':
        $pageTitle = '音效管理';
        require_once __DIR__ . '/view/public/header.php';
        require_once __DIR__ . '/view/admin/audio_mgr.php';
        require_once __DIR__ . '/view/public/footer.php';
        break;
    case 'data_export':
        $pageTitle = '数据导入导出';
        require_once __DIR__ . '/view/public/header.php';
        require_once __DIR__ . '/view/admin/data_export.php';
        require_once __DIR__ . '/view/public/footer.php';
        break;

    // 默认回到控制中心
    default:
        IndexCtrl::dashboard();
        break;
}