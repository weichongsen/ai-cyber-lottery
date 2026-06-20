<!-- VIP开通弹窗 -->
<div id="vip-modal" class="cyber-modal" style="display:none;">
    <div class="modal-backdrop" onclick="closeVipModal()"></div>
    <div class="modal-content cyber-glass">
        <div class="modal-header">
            <h2>🔓 开通VIP，解锁全部AI功能</h2>
            <button class="close-btn" onclick="closeVipModal()">✖</button>
        </div>
        <div class="modal-body">
            <p class="vip-desc">普通用户仅限 <?php echo VIP_FREE_PREDICT_LIMIT; ?> 次免费预测，VIP无限次使用 + 高级AI实验室策略 + 专属导出特权</p>
            <div class="vip-plans">
                <div class="vip-plan" data-months="1">
                    <h3>1个月</h3>
                    <p class="price">¥29.9</p>
                    <p>无限次预测</p>
                </div>
                <div class="vip-plan recommended" data-months="3">
                    <h3>3个月 <span class="badge">推荐</span></h3>
                    <p class="price">¥79.9</p>
                    <p>省10元</p>
                </div>
                <div class="vip-plan" data-months="12">
                    <h3>12个月</h3>
                    <p class="price">¥299</p>
                    <p>年费超值</p>
                </div>
            </div>
            <button id="confirm-vip-btn" class="cyber-btn primary full-width">立即开通</button>
        </div>
    </div>
</div>

<script>
// VIP弹窗控制
function openVipModal() {
    document.getElementById('vip-modal').style.display = 'flex';
}
function closeVipModal() {
    document.getElementById('vip-modal').style.display = 'none';
}

// 选择套餐
document.querySelectorAll('.vip-plan').forEach(plan => {
    plan.addEventListener('click', function() {
        document.querySelectorAll('.vip-plan').forEach(p => p.classList.remove('selected'));
        this.classList.add('selected');
    });
});

// 确认开通
document.getElementById('confirm-vip-btn').addEventListener('click', async function() {
    const selected = document.querySelector('.vip-plan.selected');
    if (!selected) {
        alert('请选择一个套餐');
        return;
    }
    const months = selected.dataset.months;
    try {
        const formData = new FormData();
        formData.append('duration_months', months);
        const resp = await fetch('/api/api_export.php?action=activate_vip', {
            method: 'POST',
            body: formData
        });
        const result = await resp.json();
        if (result.success) {
            alert(result.message);
            closeVipModal();
            // 刷新VIP状态
            window.location.reload();
        } else {
            alert(result.message || '开通失败');
        }
    } catch (err) {
        alert('网络错误');
    }
});
</script>