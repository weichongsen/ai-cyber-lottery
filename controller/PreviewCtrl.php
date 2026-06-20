<?php
/**
 * 全屏页面预览独立控制器
 * 
 * 导航栏【页面预览入口】一键打开全屏预览整套界面，
 * 保留原版所有页面 DOM 和赛博朋克特效。
 */

require_once __DIR__ . '/../config/session_init.php';

class PreviewCtrl
{
    /**
     * 渲染全屏预览页面
     */
    public static function fullPreview(): void
    {
        // 可选：要求登录或管理员，但原版似乎公开预览，这里设为无需登录
        $pageTitle = '全屏预览 - AI彩票模拟预测系统';
        require_once __DIR__ . '/../view/public/header.php';
        require_once __DIR__ . '/../view/preview/full_preview.php';
        require_once __DIR__ . '/../view/public/footer.php';
    }
}