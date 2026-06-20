    </main><!-- 闭合 #app-main -->

    <!-- VIP开通弹窗模板（全局复用） -->
    <?php require_once __DIR__ . '/vip_modal.php'; ?>

    <!-- 全屏预览弹窗容器 -->
    <?php require_once __DIR__ . '/preview_modal.php'; ?>

    <!-- 底部信息 -->
    <footer class="cyber-footer">
        <p>© 2026 AI 彩票模拟预测系统 · 仅技术研究演示 · 不涉及真实投注</p>
    </footer>

    <!-- 脚本引用 -->
    <script src="/static/js/particle_background.js"></script>
    <script src="/static/js/draw_animation.js"></script>
    <script src="/static/js/echarts_render.js"></script>
    <script src="/static/js/ajax_request.js"></script>
    <script src="/static/js/preview_control.js"></script>

    <!-- 页面内联初始化脚本（用于当前页面特定逻辑） -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 导航高亮
        const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
        document.querySelectorAll('.nav-item[data-page]').forEach(link => {
            if (link.dataset.page === currentPage) {
                link.classList.add('active');
            }
        });
        // 初始化粒子背景
        if (typeof initParticleBackground === 'function') {
            initParticleBackground();
        }
        // 加载用户会话状态
        fetch('/api/api_user.php?action=session')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.user) {
                    window.currentUser = data.user;
                    // 触发自定义事件
                    document.dispatchEvent(new CustomEvent('userLoaded', { detail: data.user }));
                }
            })
            .catch(() => {});
        // 全局预览按钮事件绑定
        const previewBtn = document.getElementById('preview-btn');
        if (previewBtn) {
            previewBtn.addEventListener('click', function() {
                openFullPreview();
            });
        }
    });

    // 全屏预览函数（定义在全局）
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
    </script>
</body>
</html>