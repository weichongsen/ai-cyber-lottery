<?php
// 该页面仅管理员可访问，由 AdminCtrl 在渲染前校验权限
?>
<div class="page-container" id="admin-dashboard-page">
    <h2 class="page-title">🛡️ 管理员控制台</h2>
    <div class="admin-stats-grid" id="admin-stats">
        <div class="stat-card"><h3>总用户</h3><p id="stat-users">--</p></div>
        <div class="stat-card"><h3>VIP用户</h3><p id="stat-vip">--</p></div>
        <div class="stat-card"><h3>大乐透数据</h3><p id="stat-dlt">--</p></div>
        <div class="stat-card"><h3>双色球数据</h3><p id="stat-ssq">--</p></div>
        <div class="stat-card"><h3>总预测次数</h3><p id="stat-predictions">--</p></div>
    </div>

    <div class="admin-actions cyber-panel">
        <button class="cyber-btn" onclick="navigateTo('user_manage')">👥 用户管理</button>
        <button class="cyber-btn" onclick="navigateTo('lottery_data_mgr')">📅 开奖数据管理</button>
        <button class="cyber-btn" onclick="navigateTo('audio_mgr')">🎵 音效管理</button>
        <button class="cyber-btn" onclick="navigateTo('data_export')">📦 数据导入/导出</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/api_admin.php?action=dashboard_stats')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-users').textContent = data.data.total_users;
                document.getElementById('stat-vip').textContent = data.data.vip_users;
                document.getElementById('stat-dlt').textContent = data.data.dlt_draws;
                document.getElementById('stat-ssq').textContent = data.data.ssq_draws;
                document.getElementById('stat-predictions').textContent = data.data.total_predictions;
            }
        });
});

function navigateTo(section) {
    // 通过hash或页面参数切换管理员子页面
    window.location.href = '/?page=' + section;
}
</script>