<!-- AI 实验室页面 -->
<div class="page-container" id="ai-lab-page">
    <h2 class="page-title">🧪 高级 AI 实验室</h2>
    <p class="lab-desc">自定义权重分配，探索不同策略对预测结果的影响</p>

    <div class="lab-controls cyber-panel">
        <div class="control-group">
            <label>彩种选择</label>
            <select id="lab-lottery-type" class="cyber-select">
                <option value="dlt">大乐透</option>
                <option value="ssq">双色球</option>
            </select>
        </div>
        <div class="control-group">
            <label>预设策略</label>
            <select id="lab-strategy" class="cyber-select">
                <option value="balanced">平衡（默认）</option>
                <option value="stable">稳健</option>
                <option value="aggressive">激进</option>
                <option value="extreme">极限</option>
                <option value="custom">自定义权重</option>
            </select>
        </div>
        <div class="control-group">
            <label>蒙特卡洛模拟次数</label>
            <select id="lab-monte-carlo" class="cyber-select">
                <option value="1000">1,000</option>
                <option value="10000" selected>10,000</option>
                <option value="50000">50,000</option>
                <option value="100000">100,000</option>
            </select>
        </div>
        <div id="custom-weights" style="display:none;">
            <div class="weight-sliders">
                <div class="weight-item"><label>热号权重 <span id="w-hot">0.20</span></label><input type="range" min="0" max="100" value="20" class="weight-slider" data-key="hot"></div>
                <div class="weight-item"><label>冷号权重 <span id="w-cold">0.10</span></label><input type="range" min="0" max="100" value="10" class="weight-slider" data-key="cold"></div>
                <div class="weight-item"><label>遗漏权重 <span id="w-missing">0.20</span></label><input type="range" min="0" max="100" value="20" class="weight-slider" data-key="missing"></div>
                <div class="weight-item"><label>连号权重 <span id="w-consecutive">0.10</span></label><input type="range" min="0" max="100" value="10" class="weight-slider" data-key="consecutive"></div>
                <div class="weight-item"><label>马尔可夫 <span id="w-markov">0.20</span></label><input type="range" min="0" max="100" value="20" class="weight-slider" data-key="markov"></div>
                <div class="weight-item"><label>蒙特卡洛 <span id="w-monte_carlo">0.15</span></label><input type="range" min="0" max="100" value="15" class="weight-slider" data-key="monte_carlo"></div>
                <div class="weight-item"><label>贝叶斯 <span id="w-bayesian">0.05</span></label><input type="range" min="0" max="100" value="5" class="weight-slider" data-key="bayesian"></div>
            </div>
        </div>
        <button class="cyber-btn primary" onclick="runLabPredict()">🚀 执行实验室预测</button>
    </div>

    <div id="lab-result" class="result-area" style="margin-top:20px;"></div>
</div>

<script>
// 策略切换显示自定义权重
document.getElementById('lab-strategy').addEventListener('change', function(e) {
    const val = e.target.value;
    document.getElementById('custom-weights').style.display = val === 'custom' ? 'block' : 'none';
});

// 滑块实时更新显示
document.querySelectorAll('.weight-slider').forEach(slider => {
    slider.addEventListener('input', function() {
        const key = this.dataset.key;
        const val = (parseInt(this.value) / 100).toFixed(2);
        document.getElementById('w-' + key).textContent = val;
    });
});

async function runLabPredict() {
    if (!window.currentUser) {
        alert('请先登录');
        window.location.href = '/?page=login';
        return;
    }
    const lotteryType = document.getElementById('lab-lottery-type').value;
    const strategy = document.getElementById('lab-strategy').value;
    const monteCarlo = document.getElementById('lab-monte-carlo').value;

    let userWeights = null;
    if (strategy === 'custom') {
        const weights = {};
        document.querySelectorAll('.weight-slider').forEach(slider => {
            weights[slider.dataset.key] = parseInt(slider.value) / 100;
        });
        userWeights = JSON.stringify(weights);
    }

    const formData = new FormData();
    formData.append('lottery_type', lotteryType);
    formData.append('predict_type', 'lab');
    formData.append('strategy', strategy === 'custom' ? null : strategy); // 字符串策略
    formData.append('monte_carlo_runs', monteCarlo);
    if (userWeights) formData.append('user_weights', userWeights);

    try {
        const resp = await fetch('/api/api_predict.php?action=predict', { method: 'POST', body: formData });
        const data = await resp.json();
        if (data.success) {
            renderLabResult(data);
        } else {
            alert(data.message);
        }
    } catch(e) { alert('网络错误'); }
}

function renderLabResult(data) {
    const container = document.getElementById('lab-result');
    let html = '<h3>预测结果</h3>';
    html += `<p>使用权重: ${JSON.stringify(data.weight_used)} | 平均评分: ${data.avg_score} ${data.avg_rating}</p>`;
    data.predictions.forEach(pred => {
        html += `<div class="pred-group">
            <div class="pred-main">${pred.main.map(n => `<span class="ball">${n}</span>`).join('')}</div>
            <div class="pred-special">${pred.special.map(n => `<span class="ball special">${n}</span>`).join('')}</div>
            <div>评分: ${pred.score} ${pred.rating}</div>
        </div>`;
    });
    container.innerHTML = html;
}
</script>