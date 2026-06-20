<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'AI彩票模拟预测系统'; ?></title>
    <meta name="description" content="AI彩票预测专家 Pro Max - 赛博朋克风格数据可视化分析系统，仅供技术研究演示">
    <meta name="robots" content="noindex, nofollow">
    <!-- 全局赛博朋克样式 -->
    <link rel="stylesheet" href="/static/css/cyber_global.css">
    <link rel="stylesheet" href="/static/css/echarts_cyber.css">
    <!-- ECharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
</head>
<body class="cyber-body">
    <!-- 全局免责声明 -->
    <div id="global-disclaimer" class="cyber-disclaimer">
        <span class="disclaimer-icon">⚠️</span>
        <span class="disclaimer-text">本系统仅用于历史开奖数据统计分析、数据可视化、模拟摇奖娱乐，不宣传、不暗示、不保证能够精准预测未来彩票开奖结果，仅作技术研究演示。</span>
    </div>

    <!-- 星空粒子背景容器 -->
    <canvas id="particle-canvas"></canvas>

    <!-- 顶部导航栏 -->
    <nav id="cyber-nav" class="cyber-nav">
        <div class="nav-logo">
            <span class="logo-text">🎰 AI 彩票预测专家 Pro Max</span>
        </div>
        <div class="nav-links" id="nav-links">
            <a href="/?page=dashboard" class="nav-item" data-page="dashboard">🏠 AI控制中心</a>
            <a href="/?page=dlt" class="nav-item" data-page="dlt">🎯 大乐透</a>
            <a href="/?page=ssq" class="nav-item" data-page="ssq">🔴 双色球</a>
            <a href="/?page=ai_lab" class="nav-item" data-page="ai_lab">🧪 AI实验室</a>
            <a href="/?page=chart_view" class="nav-item" data-page="chart_view">📊 图表中心</a>
            <a href="/?page=draw_sim" class="nav-item" data-page="draw_sim">🎰 摇奖模拟</a>
            <a href="/?page=collect" class="nav-item" data-page="collect">⭐ 收藏</a>
            <a href="/?page=predict_history" class="nav-item" data-page="predict_history">📜 预测历史</a>
            <a href="/?page=system_setting" class="nav-item" data-page="system_setting">⚙️ 设置</a>
            <?php if (isAdmin()): ?>
            <a href="/?page=admin_dash" class="nav-item admin-only" data-page="admin_dash">🛡️ 管理后台</a>
            <?php endif; ?>
        </div>
        <div class="nav-actions">
            <button id="preview-btn" class="cyber-btn small" title="全屏预览">🖥️ 预览</button>
            <?php if (isLoggedIn()): ?>
                <span class="user-badge">👤 <?php echo htmlspecialchars($_SESSION[SESSION_USER_KEY]['username']); ?></span>
                <a href="/api/api_user.php?action=logout" class="nav-item logout-link">🚪 退出</a>
            <?php else: ?>
                <a href="/?page=login" class="nav-item">🔑 登录</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- 主内容区 -->
    <main id="app-main" class="cyber-main">