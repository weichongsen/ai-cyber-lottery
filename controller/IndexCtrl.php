<?php
/**
 * 首页与彩种专区控制器 - 渲染页面视图
 */

require_once __DIR__ . '/../config/session_init.php';

class IndexCtrl
{
    /**
     * 渲染首页仪表盘
     */
    public static function dashboard(): void
    {
        // 无需登录即可访问（仅展示公开内容）
        $pageTitle = 'AI彩票模拟预测系统 - AI控制中心';
        // 引入公共头部
        require_once __DIR__ . '/../view/public/header.php';
        // 引入仪表盘视图
        require_once __DIR__ . '/../view/home/dashboard.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 大乐透专区
     */
    public static function dlt(): void
    {
        $pageTitle = '大乐透AI预测专区';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/dlt.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 双色球专区
     */
    public static function ssq(): void
    {
        $pageTitle = '双色球AI预测专区';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/ssq.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * AI实验室页面
     */
    public static function aiLab(): void
    {
        requireLoginForPage();
        $pageTitle = '高级AI实验室';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/ai_lab.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * ECharts图表可视化中心
     */
    public static function chartView(): void
    {
        $pageTitle = 'AI全维度分析图表';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/chart_view.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 摇奖模拟中心
     */
    public static function drawSim(): void
    {
        $pageTitle = '摇奖模拟中心';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/draw_sim.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 收藏中心
     */
    public static function collect(): void
    {
        requireLoginForPage();
        $pageTitle = '我的收藏中心';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/collect.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 预测历史
     */
    public static function predictHistory(): void
    {
        requireLoginForPage();
        $pageTitle = 'AI预测历史';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/predict_history.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 系统设置
     */
    public static function systemSetting(): void
    {
        requireLoginForPage();
        $pageTitle = '系统设置';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/home/system_setting.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 登录页面
     */
    public static function loginPage(): void
    {
        $pageTitle = '登录 - AI彩票模拟预测系统';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/auth/login.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }

    /**
     * 注册页面
     */
    public static function registerPage(): void
    {
        $pageTitle = '注册 - AI彩票模拟预测系统';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/auth/register.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }
}