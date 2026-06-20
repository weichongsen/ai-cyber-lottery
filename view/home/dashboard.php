<!-- AI控制中心仪表盘 -->
<div class="page-container" id="dashboard-page">
    <div class="cyber-hud-header">
        <h1 class="hud-title glitch-text" data-text="AI 控制中心">AI 控制中心</h1>
        <div class="hud-status">
            <span class="status-dot online"></span> 系统运行中 · 数据实时同步
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- 快速入口卡片 -->
        <div class="cyber-card card-dlt" onclick="window.location='/?page=dlt'">
            <div class="card-icon">🎯</div>
            <h3>大乐透预测</h3>
            <p>前区35选5 + 后区12选2</p>
            <span class="card-arrow">→</span>
        </div>
        <div class="cyber-card card-ssq" onclick="window.location='/?page=ssq'">
            <div class="card-icon">🔴</div>
            <h3>双色球预测</h3>
            <p>红球33选6 + 蓝球16选1</p>
            <span class="card-arrow">→</span>
        </div>
        <div class="cyber-card card-lab" onclick="window.location='/?page=ai_lab'">
            <div class="card-icon">🧪</div>
            <h3>AI 实验室</h3>
            <p>自定义权重 · 高级策略</p>
            <span class="card-arrow">→</span>
        </div>
        <div class="cyber-card card-chart" onclick="window.location='/?page=chart_view'">
            <div class="card-icon">📊</div>
            <h3>图表分析中心</h3>
            <p>热号、冷号、走势全维度</p>
            <span class="card-arrow">→</span>
        </div>
        <div class="cyber-card card-draw" onclick="window.location='/?page=draw_sim'">
            <div class="card-icon">🎰</div>
            <h3>摇奖模拟</h3>
            <p>滚筒动画 · 音效体验</p>
            <span class="card-arrow">→</span>
        </div>
        <div class="cyber-card card-collect" onclick="window.location='/?page=collect'">
            <div class="card-icon">⭐</div>
            <h3>我的收藏</h3>
            <p>管理心仪号码</p>
            <span class="card-arrow">→</span>
        </div>
    </div>

    <!-- AI 核心旋转球体动画（纯装饰） -->
    <div class="ai-core-sphere">
        <div class="sphere-outer"></div>
        <div class="sphere-middle"></div>
        <div class="sphere-inner"></div>
        <div class="sphere-pulse"></div>
    </div>

    <!-- 实时统计信息 -->
    <div class="stats-strip" id="dashboard-stats">
        <div class="stat-item"><span class="stat-label">大乐透已分析期数</span><span class="stat-value" id="stat-dlt">--</span></div>
        <div class="stat-item"><span class="stat-label">双色球已分析期数</span><span class="stat-value" id="stat-ssq">--</span></div>
        <div class="stat-item"><span class="stat-label">用户预测总数</span><span class="stat-value" id="stat-predictions">--</span></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 获取统计数据（后端需提供统计接口，简单起见用管理员stats或新接口）
    fetch('/api/api_admin.php?action=dashboard_stats')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-dlt').textContent = data.data.dlt_draws || '0';
                document.getElementById('stat-ssq').textContent = data.data.ssq_draws || '0';
                document.getElementById('stat-predictions').textContent = data.data.total_predictions || '0';
            }
        }).catch(() => {});
});
</script>