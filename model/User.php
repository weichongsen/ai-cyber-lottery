<?php
/**
 * 用户模型 - 处理用户注册、登录、信息查询、VIP状态更新
 */

require_once __DIR__ . '/../config/db_supabase.php';

class User
{
    /**
     * 用户注册
     *
     * @param string $username
     * @param string $email
     * @param string $password 明文密码，内部使用 bcrypt 哈希
     * @return array 包含 success 与 user_id 或 message
     */
    public static function register(string $username, string $email, string $password): array
    {
        $pdo = getDBConnection();

        // 检查用户名或邮箱是否已存在
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => '用户名或邮箱已存在'];
        }

        // 密码哈希
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // 插入新用户
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, is_vip, free_predictions_used) VALUES (?, ?, ?, 'user', FALSE, 0)");
        $stmt->execute([$username, $email, $passwordHash]);

        return ['success' => true, 'user_id' => $pdo->lastInsertId()];
    }

    /**
     * 用户登录验证
     *
     * @param string $login 用户名或邮箱
     * @param string $password
     * @return array 成功返回用户数据，失败返回消息
     */
    public static function login(string $login, string $password): array
    {
        $pdo = getDBConnection();

        // 支持用户名或邮箱登录
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => '用户名或密码错误'];
        }

        // 移除密码哈希，不返回前端
        unset($user['password_hash']);
        return ['success' => true, 'user' => $user];
    }

    /**
     * 通过ID获取用户信息
     *
     * @param int $userId
     * @return array|null
     */
    public static function getById(int $userId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, username, email, role, is_vip, vip_expire_date, free_predictions_used, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * 获取所有用户（管理员用）
     *
     * @return array
     */
    public static function getAllUsers(): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, username, email, role, is_vip, vip_expire_date, free_predictions_used, created_at FROM users ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 更新用户的免费预测使用次数（加1）
     *
     * @param int $userId
     * @return void
     */
    public static function incrementFreePrediction(int $userId): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET free_predictions_used = free_predictions_used + 1 WHERE id = ?");
        $stmt->execute([$userId]);
    }

    /**
     * 重置免费预测次数（如管理员重置或VIP开通后）
     *
     * @param int $userId
     * @return void
     */
    public static function resetFreePredictions(int $userId): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET free_predictions_used = 0 WHERE id = ?");
        $stmt->execute([$userId]);
    }

    /**
     * 开通或取消VIP
     *
     * @param int $userId
     * @param bool $isVip
     * @param string|null $expireDate VIP过期时间（可选）
     * @return void
     */
    public static function setVipStatus(int $userId, bool $isVip, ?string $expireDate = null): void
    {
        $pdo = getDBConnection();
        if ($isVip) {
            $stmt = $pdo->prepare("UPDATE users SET is_vip = TRUE, vip_expire_date = ?, free_predictions_used = 0 WHERE id = ?");
            $stmt->execute([$expireDate, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET is_vip = FALSE, vip_expire_date = NULL WHERE id = ?");
            $stmt->execute([$userId]);
        }
    }

    /**
     * 更新用户个人资料（暂不开放修改密码，仅扩展）
     *
     * @param int $userId
     * @param array $data 允许更新的字段 username, email
     * @return bool
     */
    public static function updateProfile(int $userId, array $data): bool
    {
        $pdo = getDBConnection();
        $updates = [];
        $params = [];
        foreach (['username', 'email'] as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}