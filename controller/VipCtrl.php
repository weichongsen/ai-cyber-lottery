<?php
/**
 * VIP 控制器 - VIP开通、权限校验
 */

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/VipRecord.php';
require_once __DIR__ . '/../config/session_init.php';

class VipCtrl
{
    /**
     * 检查当前用户VIP状态 (GET)
     */
    public static function checkStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'is_vip' => false]);
            return;
        }
        $user = User::getById(getCurrentUserId());
        echo json_encode([
            'success' => true,
            'is_vip' => (bool) $user['is_vip'],
            'free_used' => $user['free_predictions_used'],
            'free_limit' => VIP_FREE_PREDICT_LIMIT,
            'vip_expire' => $user['vip_expire_date']
        ]);
    }

    /**
     * VIP开通（模拟支付，实际由管理员操作或对接支付后调用）
     * 此处简化：直接调用设置VIP，并记录日志
     * POST参数: duration_months (1,3,12等)
     */
    public static function activate(): void
    {
        requireLoginJSON();
        $months = intval($_POST['duration_months'] ?? 1);
        $months = max(1, min(12, $months));
        $expireDate = date('Y-m-d H:i:s', strtotime("+{$months} months"));

        $userId = getCurrentUserId();
        User::setVipStatus($userId, true, $expireDate);
        // 记录日志（操作人设为当前用户自身，表示自助开通，也可标记系统）
        VipRecord::add($userId, 'activate', $userId, "自助开通 {$months} 个月");
        // 刷新会话
        $_SESSION[SESSION_USER_KEY]['is_vip'] = true;

        echo json_encode(['success' => true, 'message' => 'VIP已开通，有效期至 ' . $expireDate]);
    }
}