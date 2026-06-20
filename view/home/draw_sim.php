<!-- 摇奖模拟中心 -->
<div class="page-container" id="draw-sim-page">
    <h2 class="page-title">🎰 摇奖模拟中心</h2>
    <div class="draw-controls cyber-panel">
        <div class="control-group">
            <label>彩种</label>
            <select id="draw-lottery-type" class="cyber-select">
                <option value="dlt">大乐透</option>
                <option value="ssq">双色球</option>
            </select>
        </div>
        <button class="cyber-btn primary" onclick="startDraw()">🎲 开始摇奖</button>
        <button class="cyber-btn" onclick="toggleDrawSound()" id="sound-toggle">🔊 音效: 开</button>
    </div>

    <div class="draw-machine">
        <div id="draw-balls-container" class="draw-balls">
            <!-- 号码球动态渲染 -->
            <div class="draw-placeholder">点击"开始摇奖"查看模拟开奖</div>
        </div>
        <div class="draw-status" id="draw-status"></div>
    </div>
    <div id="draw-result-final" class="result-area" style="margin-top:20px;"></div>
</div>

<script>
let soundEnabled = true;
let drawAnimationRunning = false;

function toggleDrawSound() {
    soundEnabled = !soundEnabled;
    document.getElementById('sound-toggle').textContent = soundEnabled ? '🔊 音效: 开' : '🔇 音效: 关';
}

async function startDraw() {
    if (drawAnimationRunning) return;
    drawAnimationRunning = true;
    const lotteryType = document.getElementById('draw-lottery-type').value;

    try {
        const resp = await fetch(`/api/api_draw.php?action=simulate&lottery_type=${lotteryType}`);
        const data = await resp.json();
        if (data.success) {
            await runDrawAnimation(data.ball_sequence);
        } else {
            alert('摇奖失败');
        }
    } catch(e) {
        alert('网络错误');
    }
    drawAnimationRunning = false;
}

// 动画时序控制（每球3秒旋转 → 4秒定格，完全串行）
async function runDrawAnimation(sequence) {
    const container = document.getElementById('draw-balls-container');
    const statusEl = document.getElementById('draw-status');
    const resultEl = document.getElementById('draw-result-final');
    container.innerHTML = '';
    resultEl.innerHTML = '';
    const ballElements = [];

    for (let i = 0; i < sequence.length; i++) {
        const ball = sequence[i];
        // 创建球元素
        const ballDiv = document.createElement('div');
        ballDiv.className = `draw-ball ${ball.type === 'main' ? 'main-ball' : 'special-ball'} spinning`;
        ballDiv.textContent = ball.number;
        container.appendChild(ballDiv);
        ballElements.push(ballDiv);
        statusEl.textContent = `正在生成第 ${i+1} 个号码...`;

        // 旋转3秒
        await sleep(3000);
        ballDiv.classList.remove('spinning');
        ballDiv.classList.add('settled');
        // 播放音效
        if (soundEnabled) playDrawSound();

        // 定格4秒（与下一球开始生成可重叠？原需求：“上一球完全定格后再生成下一球”）
        // 所以直接在这里再等4秒
        await sleep(4000);
        statusEl.textContent = i < sequence.length - 1 ? `准备生成第 ${i+2} 个号码...` : '开奖完成！';
    }

    // 显示最终结果
    const mainNums = sequence.filter(b => b.type === 'main').map(b => b.number);
    const specialNums = sequence.filter(b => b.type === 'special').map(b => b.number);
    resultEl.innerHTML = `<div class="final-result">
        <h3>开奖号码</h3>
        <div>${mainNums.map(n => `<span class="ball">${n}</span>`).join(' ')} + ${specialNums.map(n => `<span class="ball special">${n}</span>`).join(' ')}</div>
    </div>`;
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function playDrawSound() {
    // 简易实现：使用Web Audio API播放短音效，或预加载音频文件
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = 800;
        gain.gain.value = 0.1;
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        setTimeout(() => { osc.stop(); ctx.close(); }, 100);
    } catch(e) {}
}
</script>