<!-- 预测历史 -->
<div class="page-container" id="predict-history-page">
    <h2 class="page-title">📜 AI 预测历史</h2>
    <div class="history-controls cyber-panel">
        <select id="history-filter-type" class="cyber-select" onchange="loadHistory()">
            <option value="">全部</option>
            <option value="dlt">大乐透</option>
            <option value="ssq">双色球</option>
        </select>
        <button class="cyber-btn small danger" onclick="batchDeleteHistory()">🗑️ 批量删除</button>
        <button class="cyber-btn small" onclick="clearAllHistory()">🧹 清空历史</button>
    </div>
    <table id="history-table" class="cyber-table">
        <thead><tr><th><input type="checkbox" id="select-all-history" onclick="toggleSelectAll('history-checkbox', this)"></th><th>彩种</th><th>预测类型</th><th>号码</th><th>评分</th><th>评级</th><th>时间</th></tr></thead>
        <tbody></tbody>
    </table>
    <div id="history-pagination" class="pagination"></div>
</div>

<script>
let historyPage = 1;

async function loadHistory(page = 1) {
    historyPage = page;
    const type = document.getElementById('history-filter-type').value;
    try {
        const params = new URLSearchParams({ lottery_type: type, page: page });
        const resp = await fetch('/api/api_export.php?action=list_predict_history&' + params.toString());
        const data = await resp.json();
        if (data.success) {
            renderHistoryTable(data.data);
            // 假设接口返回 total，这里简化分页
        }
    } catch(e) {}
}

function renderHistoryTable(rows) {
    const tbody = document.querySelector('#history-table tbody');
    tbody.innerHTML = rows.map(r => {
        const numbers = Array.isArray(r.numbers) ? r.numbers.map(arr => arr.join(', ')).join(' | ') : '';
        return `<tr>
            <td><input type="checkbox" class="history-checkbox" value="${r.id}"></td>
            <td>${r.lottery_type === 'dlt' ? '大乐透' : '双色球'}</td>
            <td>${r.predict_type}</td>
            <td>${numbers}</td>
            <td>${r.ai_score || '-'}</td>
            <td>${r.rating || '-'}</td>
            <td>${r.created_at}</td>
        </tr>`;
    }).join('');
}

async function batchDeleteHistory() {
    const selected = Array.from(document.querySelectorAll('.history-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) { alert('请选择'); return; }
    const formData = new FormData();
    formData.append('ids', JSON.stringify(selected));
    const resp = await fetch('/api/api_export.php?action=delete_predict_history', { method: 'POST', body: formData });
    const data = await resp.json();
    if (data.success) { loadHistory(historyPage); }
}

async function clearAllHistory() {
    if (!confirm('确定清空所有预测历史？')) return;
    const resp = await fetch('/api/api_export.php?action=clear_predict_history', { method: 'POST' });
    const data = await resp.json();
    if (data.success) { loadHistory(1); }
}

document.addEventListener('DOMContentLoaded', () => loadHistory());
</script>