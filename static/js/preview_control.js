/**
 * 全屏预览弹窗控制
 * 依赖：页面需存在 #preview-modal 和 #preview-iframe
 * 提供全局函数 openFullPreview() 和 closeFullPreview()
 */

function openFullPreview() {
    const modal = document.getElementById('preview-modal');
    if (modal) {
        modal.style.display = 'flex';
        const iframe = document.getElementById('preview-iframe');
        if (iframe) {
            iframe.src = '/?page=full_preview';
        }
    }
}

function closeFullPreview() {
    const modal = document.getElementById('preview-modal');
    if (modal) {
        modal.style.display = 'none';
        const iframe = document.getElementById('preview-iframe');
        if (iframe) iframe.src = '';
    }
}