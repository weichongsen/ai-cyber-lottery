<!-- 双色球预测专区 -->
<div class="page-container" id="ssq-page">
    <h2 class="page-title">🔴 双色球 AI 预测</h2>
    <div class="lottery-info">
        <span>红球 1-33 选 6</span> | <span>蓝球 1-16 选 1</span>
    </div>

    <div class="predict-controls">
        <button class="cyber-btn" onclick="predictSsq('single')">单组预测</button>
        <button class="cyber-btn" onclick="predictSsq('multi_5')">预测 5 组</button>
        <button class="cyber-btn" onclick="predictSsq('multi_10')">预测 10 组</button>
        <button class="cyber-btn random-btn" onclick="randomSsq()">🎲 随机选号</button>
    </div>

    <div id="ssq-result" class="result-area" style="margin-top:20px;"></div>
    <div class="action-bar" id="ssq-actions" style="display:none; margin-top:15px;">
        <button class="cyber-btn small" onclick="collectCurrent('ssq')">⭐ 收藏</button>
        <button class="cyber-btn small" onclick="exportCurrent('ssq')">📥 导出</button>
    </div>
</div>

<script>
let currentSsqResult = null;

async function predictSsq(type) {
    if (!window.currentUser) {
        alert('请先登录');
        window.location.href = '/?page=login';
        return;
    }
    try {
        const formData = new FormData();
        formData.append('lottery_type', 'ssq');
        formData.append('predict_type', type);
        const resp = await fetch('/api/api_predict.php?action=predict', { method: 'POST', body: formData });
        const data = await resp.json();
        if (data.success) {
            currentSsqResult = data;
            renderSsqResult(data);
            document.getElementById('ssq-actions').style.display = 'flex';
        } else {
            alert(data.message || '预测失败');
            if (data.message.includes('VIP') || data.message.includes('次数')) {
                openVipModal();
            }
        }
    } catch(e) { alert('网络错误'); }
}

function randomSsq() {
    const red = [], blue = [];
    while(red.length < 6) {
        let n = Math.floor(Math.random() * 33) + 1;
        if (!red.includes(n)) red.push(n);
    }
    blue.push(Math.floor(Math.random() * 16) + 1);
    red.sort((a,b)=>a-b);
    currentSsqResult = {
        predictions: [{ main: red, special: blue, score: 'N/A', rating: '随机' }]
    };
    renderSsqResult(currentSsqResult);
    document.getElementById('ssq-actions').style.display = 'flex';
}

function renderSsqResult(data) {
    const container = document.getElementById('ssq-result');
    let html = '<div class="prediction-groups">';
    data.predictions.forEach(pred => {
        html += `<div class="pred-group">
            <div class="pred-main">${pred.main.map(n => `<span class="ball red-ball">${n}</span>`).join('')}</div>
            <div class="pred-special">${pred.special.map(n => `<span class="ball blue-ball">${n}</span>`).join('')}</div>
            <div class="pred-score">评分: ${pred.score} | 评级: <span class="rating ${pred.rating}">${pred.rating}</span></div>
        </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

// 注意：collectCurrent 和 exportCurrent 已在 dlt 页面定义，这里复用同名函数需注意冲突。
// 实际应合并或使用不同函数名。为清晰，在双色球页面我们定义 collectSsq 等。
// 更优雅的做法是将收藏/导出逻辑提取到公共 JS，但此处保持每个页面独立以便阅读。
async function collectSsq() {
    if (!currentSsqResult) return;
    const pred = currentSsqResult.predictions[0];
    try {
        const formData = new FormData();
        formData.append('lottery_type', 'ssq');
        formData.append('numbers', JSON.stringify(pred.main));
        formData.append('special_numbers', JSON.stringify(pred.special));
        const resp = await fetch('/api/api_export.php?action=add_collect', { method: 'POST', body: formData });
        const data = await resp.json();
        alert(data.success ? '收藏成功' : '失败');
    } catch(e) { alert('网络错误'); }
}

function exportSsq() {
    if (!currentSsqResult) return;
    const pred = currentSsqResult.predictions[0];
    const text = `双色球预测号码: ${pred.main.join(',')} + ${pred.special.join(',')}\n评分: ${pred.score} ${pred.rating}`;
    const blob = new Blob([text], {type: 'text/plain'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `ssq_predict_${Date.now()}.txt`;
    a.click();
}
// 重新绑定按钮事件
document.querySelector('#ssq-page .action-bar button:first-child').onclick = collectSsq;
document.querySelector('#ssq-page .action-bar button:last-child').onclick = exportSsq;
</script>