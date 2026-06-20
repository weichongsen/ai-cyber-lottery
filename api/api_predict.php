<?php
/**
 * AI 预测相关 AJAX 接口
 * 路由：/api/api_predict.php?action=xxx
 */

require_once __DIR__ . '/../config/session_init.php';
require_once __DIR__ . '/../controller/AiPredictCtrl.php';
require_once __DIR__ . '/../controller/ChartCtrl.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'predict':
        // 执行预测 (POST)
        AiPredictCtrl::predict();
        break;

    case 'analysis':
        // 全维度分析数据 (GET)
        AiPredictCtrl::analysis();
        break;

    case 'hot_cold_chart':
        // 热号/冷号图表数据 (GET)
        ChartCtrl::hotColdMissing();
        break;

    case 'trend_chart':
        // 走势图数据 (GET)
        ChartCtrl::trendData();
        break;

    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
}