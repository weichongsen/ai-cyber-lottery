<?php
/**
 * 管理员后台控制器
 */

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/LotteryData.php';
require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . '/../model/VipRecord.php';
require_once __DIR__ . '/../model/AudioFile.php'; // 假设我们有一个简单的音频模型，若没有则跳过
require_once __DIR__ . '/../config/session_init.php';

class AdminCtrl
{
    /**
     * 管理员仪表盘统计
     */
    public static function dashboard(): void
    {
        requireAdminJSON();
        $stats = Admin::getDashboardStats();
        echo json_encode(['success' => true, 'data' => $stats]);
    }

    /**
     * 获取所有用户 (GET)
     */
    public static function listUsers(): void
    {
        requireAdminJSON();
        $users = User::getAllUsers();
        echo json_encode(['success' => true, 'data' => $users]);
    }

    /**
     * 切换用户VIP状态 (POST)
     * 参数: user_id, action (activate|cancel), reason
     */
    public static function toggleVip(): void
    {
        requireAdminJSON();
        $userId = intval($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $reason = $_POST['reason'] ?? '';
        if ($userId <= 0 || !in_array($action, ['activate', 'cancel'])) {
            echo json_encode(['success' => false, 'message' => '参数错误']);
            return;
        }

        $adminId = getCurrentUserId();
        if ($action === 'activate') {
            User::setVipStatus($userId, true, date('Y-m-d H:i:s', strtotime('+1 month')));
            VipRecord::add($userId, 'activate', $adminId, $reason);
        } else {
            User::setVipStatus($userId, false);
            VipRecord::add($userId, 'cancel', $adminId, $reason);
        }
        echo json_encode(['success' => true, 'message' => 'VIP状态已更新']);
    }

    /**
     * 手动导入开奖数据 (POST)
     * 参数: lottery_type, draw_num, draw_date, numbers (JSON), special_numbers (JSON)
     */
    public static function addDraw(): void
    {
        requireAdminJSON();
        $lotteryType = $_POST['lottery_type'] ?? '';
        $drawNum = $_POST['draw_num'] ?? '';
        $drawDate = $_POST['draw_date'] ?? '';
        $numbers = json_decode($_POST['numbers'] ?? '[]', true);
        $specialNumbers = json_decode($_POST['special_numbers'] ?? '[]', true);

        if (!in_array($lotteryType, ['dlt', 'ssq']) || empty($drawNum) || empty($drawDate) || empty($numbers) || empty($specialNumbers)) {
            echo json_encode(['success' => false, 'message' => '参数不完整']);
            return;
        }

        $result = LotteryData::insertDraw($lotteryType, $drawNum, $drawDate, $numbers, $specialNumbers);
        if ($result) {
            echo json_encode(['success' => true, 'message' => '开奖数据已添加']);
        } else {
            echo json_encode(['success' => false, 'message' => '添加失败，可能期号已存在']);
        }
    }

    /**
     * 更新开奖数据 (POST)
     */
    public static function updateDraw(): void
    {
        requireAdminJSON();
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID错误']);
            return;
        }
        $data = [];
        if (isset($_POST['draw_num'])) $data['draw_num'] = $_POST['draw_num'];
        if (isset($_POST['draw_date'])) $data['draw_date'] = $_POST['draw_date'];
        if (isset($_POST['numbers'])) $data['numbers'] = json_decode($_POST['numbers'], true);
        if (isset($_POST['special_numbers'])) $data['special_numbers'] = json_decode($_POST['special_numbers'], true);

        $result = LotteryData::updateDraw($id, $data);
        echo json_encode(['success' => $result, 'message' => $result ? '更新成功' : '未更新']);
    }

    /**
     * 删除开奖数据 (POST)
     */
    public static function deleteDraw(): void
    {
        requireAdminJSON();
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID错误']);
            return;
        }
        LotteryData::deleteDraw($id);
        echo json_encode(['success' => true, 'message' => '已删除']);
    }

    /**
     * 批量导入开奖数据 (POST)
     * 参数: data (JSON数组)
     */
    public static function bulkImportDraws(): void
    {
        requireAdminJSON();
        $json = $_POST['data'] ?? '';
        $draws = json_decode($json, true);
        if (!is_array($draws) || empty($draws)) {
            echo json_encode(['success' => false, 'message' => '数据格式错误']);
            return;
        }
        $imported = LotteryData::bulkInsert($draws);
        echo json_encode(['success' => true, 'imported' => $imported]);
    }

    /**
     * 导出所有数据 (GET)
     */
    public static function exportAll(): void
    {
        requireAdminJSON();
        $data = Admin::exportAllData();
        // 返回 JSON 下载或显示
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="data_export_'.date('YmdHis').'.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 导入数据 (POST)
     * 参数: data_file (上传文件) 或 data_json (文本)
     */
    public static function importAll(): void
    {
        requireAdminJSON();
        $json = '';
        if (isset($_FILES['data_file']) && $_FILES['data_file']['error'] === UPLOAD_ERR_OK) {
            $json = file_get_contents($_FILES['data_file']['tmp_name']);
        } elseif (isset($_POST['data_json'])) {
            $json = $_POST['data_json'];
        } else {
            echo json_encode(['success' => false, 'message' => '未提供数据']);
            return;
        }

        $data = json_decode($json, true);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'JSON解析失败']);
            return;
        }

        $result = Admin::importAllData($data);
        echo json_encode(['success' => $result, 'message' => $result ? '导入成功' : '导入失败']);
    }

    /**
     * 音频文件管理（示例：列出）
     */
    public static function listAudio(): void
    {
        requireAdminJSON();
        // 简单从 audio_files 表查询，或者目录扫描。假设有 AudioFile 模型
        // 为展示完整，这里简单返回目录文件列表
        $audioDir = __DIR__ . '/../static/audio/';
        $files = [];
        if (is_dir($audioDir)) {
            foreach (scandir($audioDir) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $files[] = ['name' => $file, 'path' => '/static/audio/' . $file];
                }
            }
        }
        echo json_encode(['success' => true, 'data' => $files]);
    }

    /**
     * 上传音频文件
     */
    public static function uploadAudio(): void
    {
        requireAdminJSON();
        if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => '上传失败']);
            return;
        }
        $uploadDir = __DIR__ . '/../static/audio/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = basename($_FILES['audio']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['audio']['tmp_name'], $targetPath)) {
            // 记录到数据库（如果需要）
            // ...
            echo json_encode(['success' => true, 'path' => '/static/audio/' . $filename]);
        } else {
            echo json_encode(['success' => false, 'message' => '文件保存失败']);
        }
    }
}