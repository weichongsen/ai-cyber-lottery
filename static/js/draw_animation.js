/**
 * 摇奖动画辅助函数
 * 提供 sleep、playDrawSound、runDrawAnimation 等，供摇奖页面使用
 * 依赖：页面需存在 #draw-balls-container、#draw-status、#draw-result-final
 */

/**
 * 异步等待 ms 毫秒
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * 播放简单音效（Web Audio API）
 */
function playDrawSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(800, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.2);
    } catch(e) {
        // 忽略音频错误
    }
}

/**
 * 执行整个摇奖动画序列（每球3秒旋转，然后4秒定格，完全串行）
 * @param {Array} sequence - 球序列，如 [{type:'main',number:12}, ...]
 * @param {boolean} soundEnabled - 是否播放音效
 */
async function runDrawAnimation(sequence, soundEnabled = true) {
    const container = document.getElementById('draw-balls-container');
    const statusEl = document.getElementById('draw-status');
    const resultEl = document.getElementById('draw-result-final');
    if (!container || !statusEl || !resultEl) return;

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
        if (soundEnabled) playDrawSound();

        // 定格4秒
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

// 注意：此文件中定义的函数与之前页面内联脚本重复，实际部署时应移除页面内联的同名函数以避免冲突。
// 使用外部引用时，确保只在 header 中加载本文件，页面内不再定义 runDrawAnimation 等。