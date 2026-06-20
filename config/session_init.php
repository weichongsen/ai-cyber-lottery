<?php
/**
 * 会话初始化与权限校验公共方法
 * 
 * 需在所有 PHP 页面入口处引入（通过 index.php 路由统一引入）。
 * 负责启动会话、检查登录状态、管理员权限。
 */

// 启动会话（确保无输出前调用）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 引入数据库连接（若尚未引入）
require_once __DIR__ . '/db_supabase.php';

/**
 * 检查用户是否已登录
 *
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION[SESSION_USER_KEY]) && !empty($_SESSION[SESSION_USER_KEY]['id']);
}

/**
 * 获取当前登录用户ID
 *
 * @return int|null
 */
function getCurrentUserId(): ?int {
    return $_SESSION[SESSION_USER_KEY]['id'] ?? null;
}

/**
 * 检查当前用户是否为管理员
 *
 * @return bool
 */
function isAdmin(): bool {
    return isset($_SESSION[SESSION_ADMIN_KEY]) && $_SESSION[SESSION_ADMIN_KEY] === true;
}

/**
 * 要求登录，否则输出 JSON 错误并终止
 * 适用于 AJAX API 接口
 */
function requireLoginJSON(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => '请先登录']);
        exit;
    }
}

/**
 * 要求管理员权限，否则返回 JSON 错误
 */
function requireAdminJSON(): void {
    requireLoginJSON();
    if (!isAdmin()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => '需要管理员权限']);
        exit;
    }
}

/**
 * 页面视图所需权限检查（非 JSON 响应，用于页面重定向）
 * 
 * @param bool $requireAdmin 是否必须管理员
 */
function requireLoginForPage(bool $requireAdmin = false): void {
    if (!isLoggedIn()) {
        header('Location: /?page=login');
        exit;
    }
    if ($requireAdmin && !isAdmin()) {
        header('Location: /?page=dashboard');
        exit;
    }
}

/**
 * 初始化会话中的用户信息（登录成功后调用）
 *
 * @param array $user 用户数据库记录
 */
function loginUser(array $user): void {
    $_SESSION[SESSION_USER_KEY] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'email'    => $user['email'],
        'role'     => $user['role'],
        'is_vip'   => (bool) $user['is_vip'],
    ];
    $_SESSION[SESSION_ADMIN_KEY] = ($user['role'] === ROLE_ADMIN);
}

/**
 * 登出并销毁会话
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * 刷新会话中的 VIP 状态（从数据库同步）
 * 通常在 VIP 状态被管理员修改后调用，或每次访问时轻量刷新
 */
function refreshVipStatus(): void {
    if (!isLoggedIn()) return;
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT is_vip, vip_expire_date FROM users WHERE id = ?");
        $stmt->execute([getCurrentUserId()]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION[SESSION_USER_KEY]['is_vip'] = (bool) $user['is_vip'];
            // 如果 VIP 已过期，自动取消
            if ($user['is_vip'] && $user['vip_expire_date'] && strtotime($user['vip_expire_date']) < time()) {
                // 更新数据库为非 VIP
                $update = $pdo->prepare("UPDATE users SET is_vip = FALSE WHERE id = ?");
                $update->execute([getCurrentUserId()]);
                $_SESSION[SESSION_USER_KEY]['is_vip'] = false;
            }
        }
    } catch (PDOException $e) {
        error_log('VIP refresh error: ' . $e->getMessage());
    }
}

// 自动刷新 VIP 状态（每次请求执行一次）
if (isLoggedIn()) {
    // 为了避免每个请求都查库，可以缓存到会话中并设置 TTL，这里简化处理，仅首次刷新
    if (!isset($_SESSION['vip_last_refresh']) || (time() - $_SESSION['vip_last_refresh']) > 300) {
        refreshVipStatus();
        $_SESSION['vip_last_refresh'] = time();
    }
}