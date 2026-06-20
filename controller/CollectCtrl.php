<?php
/**
 * 收藏与预测历史控制器
 */

require_once __DIR__ . '/../model/Collect.php';
require_once __DIR__ . '/../model/PredictLog.php';
require_once __DIR__ . '/../config/session_init.php';

class CollectCtrl
{
    /**
     * 添加收藏 (POST)
     * 参数: lottery_type, numbers (JSON数组), special_numbers (JSON), note
     */
    public static function addCollect(): void
    {
        requireLoginJSON();
        $lotteryType = $_POST['lottery_type'] ?? '';
        $numbers = json_decode($_POST['numbers'] ?? '[]', true);
        $specialNumbers = json_decode($_POST['special_numbers'] ?? '[]', true);
        $note = $_POST['note'] ?? '';

        if (!in_array($lotteryType, ['dlt', 'ssq']) || empty($numbers) || empty($specialNumbers)) {
            echo json_encode(['success' => false, 'message' => '参数错误']);
            return;
        }

        $id = Collect::add(getCurrentUserId(), $lotteryType, $numbers, $specialNumbers, $note);
        echo json_encode(['success' => true, 'id' => $id]);
    }

    /**
     * 获取收藏列表 (GET)
     * 参数: lottery_type (可选), search (可选), page, limit
     */
    public static function listCollect(): void
    {
        requireLoginJSON();
        $lotteryType = $_GET['lottery_type'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $userId = getCurrentUserId();
        $rows = Collect::getByUser($userId, $lotteryType, $search, $limit, $offset);
        $total = Collect::countByUser($userId, $lotteryType);

        echo json_encode(['success' => true, 'data' => $rows, 'total' => $total, 'page' => $page, 'limit' => $limit]);
    }

    /**
     * 批量删除收藏 (POST)
     * 参数: ids (JSON数组)
     */
    public static function deleteCollect(): void
    {
        requireLoginJSON();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => '请选择要删除的项']);
            return;
        }
        $deleted = Collect::deleteBatch(getCurrentUserId(), $ids);
        echo json_encode(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * 获取预测历史列表 (GET)
     */
    public static function listPredictHistory(): void
    {
        requireLoginJSON();
        $lotteryType = $_GET['lottery_type'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $userId = getCurrentUserId();
        $rows = PredictLog::getByUser($userId, $lotteryType, $limit, $offset);

        echo json_encode(['success' => true, 'data' => $rows, 'page' => $page]);
    }

    /**
     * 批量删除预测历史 (POST)
     */
    public static function deletePredictHistory(): void
    {
        requireLoginJSON();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => '请选择']);
            return;
        }
        $deleted = PredictLog::deleteBatch(getCurrentUserId(), $ids);
        echo json_encode(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * 清空预测历史 (POST)
     */
    public static function clearPredictHistory(): void
    {
        requireLoginJSON();
        PredictLog::clearByUser(getCurrentUserId());
        echo json_encode(['success' => true, 'message' => '已清空']);
    }
}