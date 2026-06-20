<div class="auth-container">
    <div class="auth-card cyber-glass">
        <h2 class="auth-title">🔐 登录 AI 预测系统</h2>
        <form id="login-form" class="auth-form">
            <div class="form-group">
                <label for="login-account">用户名 / 邮箱</label>
                <input type="text" id="login-account" name="login" placeholder="请输入用户名或邮箱" required>
            </div>
            <div class="form-group">
                <label for="login-password">密码</label>
                <input type="password" id="login-password" name="password" placeholder="请输入密码" required>
            </div>
            <button type="submit" class="cyber-btn primary full-width">登录</button>
        </form>
        <p class="auth-switch">还没有账号？ <a href="/?page=register">立即注册</a></p>
        <p id="login-error" class="error-msg" style="display:none;"></p>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const login = document.getElementById('login-account').value;
    const password = document.getElementById('login-password').value;
    const errorEl = document.getElementById('login-error');
    errorEl.style.display = 'none';

    try {
        const formData = new FormData();
        formData.append('login', login);
        formData.append('password', password);
        const resp = await fetch('/api/api_user.php?action=login', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();
        if (data.success) {
            window.location.href = '/?page=dashboard';
        } else {
            errorEl.textContent = data.message || '登录失败';
            errorEl.style.display = 'block';
        }
    } catch (err) {
        errorEl.textContent = '网络错误，请稍后重试';
        errorEl.style.display = 'block';
    }
});
</script>