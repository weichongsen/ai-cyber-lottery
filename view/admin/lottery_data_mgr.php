<div class="page-container" id="lottery-data-page">
    <h2 class="page-title">📅 开奖数据管理</h2>
    
    <!-- 手动添加单条 -->
    <div class="cyber-panel">
        <h3>添加开奖数据</h3>
        <form id="add-draw-form" onsubmit="addDraw(event)">
            <select name="lottery_type" required>
                <option value="dlt">大乐透</option>
                <option value="ssq">双色球</option>
            </select>
            <input name="draw_num" placeholder="期号 (如 2024001)" required>
            <input name="draw_date" type="date" required>
            <input name="numbers" placeholder='前区/红球 (JSON数组，如 [1,5,12,23,35])' required>
            <input name="special_numbers" placeholder='后区/蓝球 (JSON数组，如 [2,9])' required>
            <button type="submit" class="cyber-btn">添加</button>
        </form>
    </div>

    <!-- 批量导入 -->
    <div class="cyber-panel" style="margin-top:20px;">
        <h3>批量导入 JSON 数据</h3>
        <textarea id="bulk-json" rows="5" placeholder='[{"lottery_type":"dlt","draw_num":"...","draw_date":"...","numbers":[...],"special_numbers":[...]}]'></textarea>
        <button class="cyber-btn" onclick="bulkImport()">批量导入</button>
    </div>

    <!-- 开奖列表 -->
    <div class="cyber-panel" style="margin-top:20px;">
        <h3>现有数据</h3>
        <select id="draw-filter-type" onchange="loadDraws()">
            <option value="dlt">大乐透</option>
            <option value="ssq">双色球</option>
        </select>
        <table id="draws-table" class="cyber-table">
            <thead><tr><th>ID</th><th>期号</th><th>日期</th><th>号码</th><th>操作</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => loadDraws());

async function loadDraws() {
    // 简单起见，直接请求最近100条（或分页），这里后端需提供管理员获取开奖列表接口
    const type = document.getElementById('draw-filter-type').value;
    // 需要添加一个新 API 接口，或复用 LotteryData 直接查询。这里用已有的 admin 接口扩展，假设我们添加了 list_draws 动作
    // 为保持完整性，我们临时使用前端直接调用 /api/api_admin.php?action=list_draws （但该接口还未实现，需补充）
    // 我们可以在 admin 控制器中添加，但此处先留一个占位，并在后续说明。现在为了演示，使用客户端 fetch 调用新加的 list_draws。
    try {
        const resp = await fetch(`/api/api_admin.php?action=list_draws&lottery_type=${type}`);
        const data = await resp.json();
        if (data.success) renderDrawsTable(data.data);
    } catch(e) {}
}

function renderDrawsTable(draws) {
    const tbody = document.querySelector('#draws-table tbody');
    tbody.innerHTML = draws.map(d => `
        <tr>
            <td>${d.id}</td>
            <td>${d.draw_num}</td>
            <td>${d.draw_date}</td>
            <td>${JSON.stringify(d.numbers)} + ${JSON.stringify(d.special_numbers)}</td>
            <td>
                <button class="cyber-btn small" onclick="editDraw(${d.id})">编辑</button>
                <button class="cyber-btn small danger" onclick="deleteDraw(${d.id})">删除</button>
            </td>
        </tr>
    `).join('');
}

async function addDraw(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const resp = await fetch('/api/api_admin.php?action=add_draw', { method: 'POST', body: formData });
    const result = await resp.json();
    alert(result.message);
    if (result.success) loadDraws();
}

async function bulkImport() {
    const jsonStr = document.getElementById('bulk-json').value;
    if (!jsonStr) { alert('请输入JSON数据'); return; }
    const formData = new FormData();
    formData.append('data', jsonStr);
    const resp = await fetch('/api/api_admin.php?action=bulk_import_draws', { method: 'POST', body: formData });
    const result = await resp.json();
    alert(result.message || `成功导入 ${result.imported} 条`);
    loadDraws();
}

async function deleteDraw(id) {
    if (!confirm('确定删除？')) return;
    const formData = new FormData();
    formData.append('id', id);
    const resp = await fetch('/api/api_admin.php?action=delete_draw', { method: 'POST', body: formData });
    const result = await resp.json();
    alert(result.message);
    loadDraws();
}

// 编辑功能略（可打开模态框修改）
function editDraw(id) {
    alert('编辑功能请自行扩展');
}
</script>