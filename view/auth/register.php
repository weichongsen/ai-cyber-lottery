<div class="auth-container">
    <div class="auth-card cyber-glass">
        <h2 class="auth-title">📝 注册新账号</h2>
        <form id="register-form" class="auth-form">
            <div class="form-group">
                <label for="reg-username">用户名</label>
                <input type="text" id="reg-username" name="username" placeholder="4-20位字符" required minlength="4" maxlength="20">
            </div>
            <div class="form-group">
                <label for="reg-email">邮箱</label>
                <input type="email" id="reg-email" name="email" placeholder="请输入有效邮箱" required>
            </div>
            <div class="form-group">
                <label for="reg-password">密码</label>
                <input type="password" id="reg-password" name="password" placeholder="至少6位" required minlength="6">
            </div>
            <div class="form-group">
                <label for="reg-password-confirm">确认密码</label>
                <input type="password" id="reg-password-confirm" name="password_confirm" placeholder="再次输入密码" required>
            </div>
            <button type="submit" class="cyber-btn primary full-width">注册</button>
        </form>
        <p class="auth-switch">已有账号？ <a href="/?page=login">去登录</a></p>
        <p id="register-error" class="error-msg" style="display:none;"></p>
    </div>
</div>

<script>
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const username = document.getElementById('reg-username').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const confirm = document.getElementById('reg-password-confirm').value;
    const errorEl = document.getElementById('register-error');
    errorEl.style.display = 'none';

    if (password !== confirm) {
        errorEl.textContent = '两次密码不一致';
        errorEl.style.display = 'block';
        return;
    }

    try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('password_confirm', confirm);
        const resp = await fetch('/api/api_user.php?action=register', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();
        if (data.success) {
            alert('注册成功，请登录');
            window.location.href = '/?page=login';
        } else {
            errorEl.textContent = data.message || '注册失败';
            errorEl.style.display = 'block';
        }
    } catch (err) {
        errorEl.textContent = '网络错误';
        errorEl.style.display = 'block';
    }
});
</script>