<!-- 全屏预览总页面，集成所有核心界面（简化版，类似原单HTML多标签展示） -->
<div class="full-preview-wrapper" style="padding:20px; color:#00F5FF;">
    <h2 style="text-align:center;">🖥️ 全屏预览 - AI 彩票预测系统 Pro Max</h2>
    <div class="preview-tabs">
        <button onclick="showPreviewTab('dashboard')">控制中心</button>
        <button onclick="showPreviewTab('dlt')">大乐透</button>
        <button onclick="showPreviewTab('ssq')">双色球</button>
        <button onclick="showPreviewTab('chart')">图表</button>
        <button onclick="showPreviewTab('draw')">摇奖</button>
    </div>
    <div id="preview-content" style="margin-top:20px;">
        <!-- 动态加载预览片段，或直接嵌入简单视图 -->
        <p>请点击上方标签切换预览区域。</p>
    </div>
</div>

<script>
// 简易预览切换，加载对应页面片段（通过iframe或直接请求接口渲染）
function showPreviewTab(tab) {
    const content = document.getElementById('preview-content');
    switch(tab) {
        case 'dashboard':
            content.innerHTML = `<iframe src="/?page=dashboard" style="width:100%;height:800px;border:none;"></iframe>`;
            break;
        case 'dlt':
            content.innerHTML = `<iframe src="/?page=dlt" style="width:100%;height:800px;border:none;"></iframe>`;
            break;
        case 'ssq':
            content.innerHTML = `<iframe src="/?page=ssq" style="width:100%;height:800px;border:none;"></iframe>`;
            break;
        case 'chart':
            content.innerHTML = `<iframe src="/?page=chart_view" style="width:100%;height:800px;border:none;"></iframe>`;
            break;
        case 'draw':
            content.innerHTML = `<iframe src="/?page=draw_sim" style="width:100%;height:800px;border:none;"></iframe>`;
            break;
        default:
            content.innerHTML = `<p>未知预览</p>`;
    }
}
// 默认显示仪表盘
showPreviewTab('dashboard');
</script>