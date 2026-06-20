<div class="page-container" id="data-export-page">
    <h2 class="page-title">📦 数据导入 / 导出</h2>
    <div class="cyber-panel">
        <h3>导出全部数据</h3>
        <p>将数据库所有表导出为 JSON 文件，可用于备份或迁移。</p>
        <button class="cyber-btn" onclick="exportAll()">⬇️ 下载 JSON</button>
    </div>
    <div class="cyber-panel" style="margin-top:20px;">
        <h3>导入数据</h3>
        <p><strong class="danger">警告：</strong>导入将覆盖现有数据库内容，请确保备份！</p>
        <input type="file" id="import-file" accept=".json">
        <button class="cyber-btn danger" onclick="importAll()">⬆️ 导入并覆盖</button>
    </div>
</div>

<script>
async function exportAll() {
    const resp = await fetch('/api/api_admin.php?action=export_all');
    const blob = await resp.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'data_export_' + new Date().toISOString().slice(0,10) + '.json';
    a.click();
    URL.revokeObjectURL(url);
}

async function importAll() {
    const fileInput = document.getElementById('import-file');
    if (!fileInput.files.length) { alert('请选择文件'); return; }
    if (!confirm('确定要覆盖全部数据吗？此操作不可逆！')) return;
    const formData = new FormData();
    formData.append('data_file', fileInput.files[0]);
    const resp = await fetch('/api/api_admin.php?action=import_all', { method: 'POST', body: formData });
    const result = await resp.json();
    alert(result.message || '导入完成');
}
</script>