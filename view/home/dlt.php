<!-- 大乐透预测专区 -->
<div class="page-container" id="dlt-page">
    <h2 class="page-title">🎯 大乐透 AI 预测</h2>
    <div class="lottery-info">
        <span>前区 1-35 选 5</span> | <span>后区 1-12 选 2</span>
    </div>

    <div class="predict-controls">
        <button class="cyber-btn" onclick="predictDlt('single')">单组预测</button>
        <button class="cyber-btn" onclick="predictDlt('multi_5')">预测 5 组</button>
        <button class="cyber-btn" onclick="predictDlt('multi_10')">预测 10 组</button>
        <button class="cyber-btn random-btn" onclick="randomDlt()">🎲 随机选号</button>
    </div>

    <div id="dlt-result" class="result-area" style="margin-top:20px;">
        <!-- 预测结果动态渲染 -->
    </div>

    <div class="action-bar" id="dlt-actions" style="display:none; margin-top:15px;">
        <button class="cyber-btn small" onclick="collectCurrent('dlt')">⭐ 收藏</button>
        <button class="cyber-btn small" onclick="exportCurrent('dlt')">📥 导出</button>
    </div>
</div>

<script>
let currentDltResult = null; // 存储当前结果，用于收藏/导出

async function predictDlt(type) {
    if (!window.currentUser) {
        alert('请先登录');
        window.location.href = '/?page=login';
        return;
    }
    try {
        const formData = new FormData();
        formData.append('lottery_type', 'dlt');
        formData.append('predict_type', type);
        const resp = await fetch('/api/api_predict.php?action=predict', { method: 'POST', body: formData });
        const data = await resp.json();
        if (data.success) {
            currentDltResult = data;
            renderDltResult(data);
            document.getElementById('dlt-actions').style.display = 'flex';
        } else {
            alert(data.message || '预测失败');
            if (data.message.includes('VIP') || data.message.includes('次数')) {
                openVipModal();
            }
        }
    } catch (e) {
        alert('网络错误');
    }
}

function randomDlt() {
    // 前端简单随机（不经过AI权重，纯娱乐）
    const main = [], special = [];
    while(main.length < 5) {
        let n = Math.floor(Math.random() * 35) + 1;
        if (!main.includes(n)) main.push(n);
    }
    while(special.length < 2) {
        let n = Math.floor(Math.random() * 12) + 1;
        if (!special.includes(n)) special.push(n);
    }
    main.sort((a,b)=>a-b); special.sort((a,b)=>a-b);
    currentDltResult = {
        predictions: [{ main: main, special: special, score: 'N/A', rating: '随机' }]
    };
    renderDltResult(currentDltResult);
    document.getElementById('dlt-actions').style.display = 'flex';
}

function renderDltResult(data) {
    const container = document.getElementById('dlt-result');
    let html = '<div class="prediction-groups">';
    data.predictions.forEach((pred, idx) => {
        html += `<div class="pred-group">
            <div class="pred-main">${pred.main.map(n => `<span class="ball main-ball">${n}</span>`).join('')}</div>
            <div class="pred-special">${pred.special.map(n => `<span class="ball special-ball">${n}</span>`).join('')}</div>
            <div class="pred-score">评分: ${pred.score} | 评级: <span class="rating ${pred.rating}">${pred.rating}</span></div>
        </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

async function collectCurrent(lotteryType) {
    if (!currentDltResult || !currentDltResult.predictions) return;
    const pred = currentDltResult.predictions[0]; // 收藏第一组
    try {
        const formData = new FormData();
        formData.append('lottery_type', lotteryType);
        formData.append('numbers', JSON.stringify(pred.main));
        formData.append('special_numbers', JSON.stringify(pred.special));
        const resp = await fetch('/api/api_export.php?action=add_collect', { method: 'POST', body: formData });
        const data = await resp.json();
        alert(data.success ? '收藏成功' : '收藏失败');
    } catch(e) { alert('网络错误'); }
}

function exportCurrent(lotteryType) {
    if (!currentDltResult || !currentDltResult.predictions) return;
    const pred = currentDltResult.predictions[0];
    const text = `大乐透预测号码: ${pred.main.join(',')} + ${pred.special.join(',')}\n评分: ${pred.score} ${pred.rating}`;
    const blob = new Blob([text], {type: 'text/plain'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `dlt_predict_${Date.now()}.txt`;
    a.click();
}
</script>