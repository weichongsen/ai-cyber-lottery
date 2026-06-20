<!-- 全屏预览弹窗 -->
<div id="preview-modal" class="cyber-modal fullscreen-preview" style="display:none;">
    <div class="modal-header preview-header">
        <h2>🖥️ 全屏预览 - AI彩票预测系统</h2>
        <button class="close-btn" onclick="closeFullPreview()">✖</button>
    </div>
    <iframe id="preview-iframe" src="" frameborder="0" style="width:100%; height:calc(100vh - 60px);"></iframe>
</div>

<style>
/* 预览弹窗专用样式 */
.fullscreen-preview {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    z-index: 10000;
    background: #0B1020;
}
.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background: rgba(0, 245, 255, 0.1);
    border-bottom: 1px solid #00F5FF;
}
</style>