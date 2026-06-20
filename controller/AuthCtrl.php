<?php
/**
 * 认证控制器 - 登录、注册、登出
 */

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../config/session_init.php';

class AuthCtrl
{
    /**
     * 处理登录请求
     * POST 参数: login (用户名或邮箱), password
     */
    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '仅支持POST请求']);
            return;
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            echo json_encode(['success' => false, 'message' => '请输入用户名/邮箱和密码']);
            return;
        }

        $result = User::login($login, $password);
        if ($result['success']) {
            loginUser($result['user']);
            echo json_encode(['success' => true, 'message' => '登录成功', 'user' => $result['user']]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    }

    /**
     * 处理注册请求
     * POST 参数: username, email, password, password_confirm
     */
    public static function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '仅支持POST请求']);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // 基本校验
        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => '所有字段不能为空']);
            return;
        }
        if ($password !== $passwordConfirm) {
            echo json_encode(['success' => false, 'message' => '两次密码输入不一致']);
            return;
        }
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => '密码长度不能少于6位']);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
            return;
        }

        $result = User::register($username, $email, $password);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => '注册成功，请登录']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    }

    /**
     * 登出
     */
    public static function logout(): void
    {
        if (isLoggedIn()) {
            logoutUser();
        }
        header('Location: /?page=login');
        exit;
    }

    /**
     * 获取当前登录用户信息（用于前端初始化）
     */
    public static function getSessionUser(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (isLoggedIn()) {
            // 刷新VIP状态（已在session_init中自动执行，但这里可再确认）
            echo json_encode([
                'success' => true,
                'user' => $_SESSION[SESSION_USER_KEY]
            ]);
        } else {
            echo json_encode(['success' => false, 'user' => null]);
        }
    }
}