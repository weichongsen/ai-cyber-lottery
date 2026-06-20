<!-- 收藏中心 -->
<div class="page-container" id="collect-page">
    <h2 class="page-title">⭐ 我的收藏</h2>
    <div class="collect-controls cyber-panel">
        <select id="collect-filter-type" class="cyber-select" onchange="loadCollectList()">
            <option value="">全部彩种</option>
            <option value="dlt">大乐透</option>
            <option value="ssq">双色球</option>
        </select>
        <input type="text" id="collect-search" placeholder="搜索号码..." class="cyber-input" oninput="loadCollectListDebounced()">
        <button class="cyber-btn small" onclick="loadCollectList()">🔍 搜索</button>
        <button class="cyber-btn small danger" onclick="batchDeleteCollect()">🗑️ 批量删除</button>
    </div>
    <table id="collect-table" class="cyber-table">
        <thead><tr><th><input type="checkbox" id="select-all-collect" onclick="toggleSelectAll('collect-checkbox', this)"></th><th>彩种</th><th>号码</th><th>特殊号</th><th>备注</th><th>时间</th></tr></thead>
        <tbody></tbody>
    </table>
    <div id="collect-pagination" class="pagination"></div>
</div>

<script>
let collectPage = 1;
let collectDebounceTimer;

function loadCollectListDebounced() {
    clearTimeout(collectDebounceTimer);
    collectDebounceTimer = setTimeout(loadCollectList, 300);
}

async function loadCollectList(page = 1) {
    collectPage = page;
    const type = document.getElementById('collect-filter-type').value;
    const search = document.getElementById('collect-search').value;
    try {
        const params = new URLSearchParams({ lottery_type: type, search: search, page: page });
        const resp = await fetch('/api/api_export.php?action=list_collect&' + params.toString());
        const data = await resp.json();
        if (data.success) {
            renderCollectTable(data.data);
            renderPagination('collect-pagination', data.page, Math.ceil(data.total / data.limit), loadCollectList);
        }
    } catch(e) {}
}

function renderCollectTable(rows) {
    const tbody = document.querySelector('#collect-table tbody');
    tbody.innerHTML = rows.map(r => {
        const main = r.numbers.join(', ');
        const special = r.special_numbers.join(', ');
        return `<tr>
            <td><input type="checkbox" class="collect-checkbox" value="${r.id}"></td>
            <td>${r.lottery_type === 'dlt' ? '大乐透' : '双色球'}</td>
            <td>${main}</td>
            <td>${special}</td>
            <td>${r.note || ''}</td>
            <td>${r.created_at}</td>
        </tr>`;
    }).join('');
}

async function batchDeleteCollect() {
    const selected = Array.from(document.querySelectorAll('.collect-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) { alert('请选择要删除的项'); return; }
    if (!confirm('确定删除？')) return;
    const formData = new FormData();
    formData.append('ids', JSON.stringify(selected));
    const resp = await fetch('/api/api_export.php?action=delete_collect', { method: 'POST', body: formData });
    const data = await resp.json();
    if (data.success) { loadCollectList(collectPage); }
}

function toggleSelectAll(className, source) {
    document.querySelectorAll('.' + className).forEach(cb => cb.checked = source.checked);
}

function renderPagination(containerId, current, totalPages, callback) {
    const container = document.getElementById(containerId);
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === current ? 'active' : ''}" onclick="${callback.name}(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => loadCollectList());
</script>