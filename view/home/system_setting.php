<!-- 系统设置页面 -->
<div class="page-container" id="setting-page">
    <h2 class="page-title">⚙️ 系统设置</h2>
    <div class="settings-panel cyber-panel">
        <div class="setting-group">
            <label>主题切换</label>
            <select id="setting-theme" class="cyber-select">
                <option value="tech_blue">科技蓝</option>
                <option value="cyberpunk">赛博朋克</option>
                <option value="ai_purple">AI 紫</option>
                <option value="deep_space">深空黑</option>
            </select>
        </div>
        <div class="setting-group">
            <label>动画开关</label>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-animations" checked>
                <span class="slider"></span> 开启动画
            </label>
        </div>
        <div class="setting-group">
            <label>音效开关</label>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-sound" checked>
                <span class="slider"></span> 开启音效
            </label>
        </div>
        <button class="cyber-btn primary" onclick="saveSettings()">💾 保存设置</button>
        <button class="cyber-btn warning" onclick="resetSettings()">🔄 重置默认</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const resp = await fetch('/api/api_export.php?action=get_settings');
        const data = await resp.json();
        if (data.success) {
            const s = data.data;
            if (s.theme) document.getElementById('setting-theme').value = s.theme;
            if (s.animations !== undefined) document.getElementById('setting-animations').checked = s.animations === '1';
            if (s.sound !== undefined) document.getElementById('setting-sound').checked = s.sound === '1';
        }
    } catch(e) {}
});

async function saveSettings() {
    const theme = document.getElementById('setting-theme').value;
    const animations = document.getElementById('setting-animations').checked ? '1' : '0';
    const sound = document.getElementById('setting-sound').checked ? '1' : '0';
    const settings = { theme, animations, sound };
    const formData = new FormData();
    formData.append('settings', JSON.stringify(settings));
    const resp = await fetch('/api/api_export.php?action=save_settings', { method: 'POST', body: formData });
    const data = await resp.json();
    alert(data.message || '设置已保存');
}

async function resetSettings() {
    const resp = await fetch('/api/api_export.php?action=reset_settings', { method: 'POST' });
    const data = await resp.json();
    if (data.success) {
        document.getElementById('setting-theme').value = 'tech_blue';
        document.getElementById('setting-animations').checked = true;
        document.getElementById('setting-sound').checked = true;
        alert('已重置');
    }
}
</script>