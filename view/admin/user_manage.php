<div class="page-container" id="user-manage-page">
    <h2 class="page-title">👥 用户管理</h2>
    <table id="users-table" class="cyber-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>用户名</th>
                <th>邮箱</th>
                <th>角色</th>
                <th>VIP状态</th>
                <th>免费预测已用</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadUsers);

async function loadUsers() {
    try {
        const resp = await fetch('/api/api_admin.php?action=list_users');
        const data = await resp.json();
        if (data.success) {
            renderUsersTable(data.data);
        }
    } catch(e) {}
}

function renderUsersTable(users) {
    const tbody = document.querySelector('#users-table tbody');
    tbody.innerHTML = users.map(u => {
        const vipStatus = u.is_vip ? '✅ VIP' : '❌ 普通';
        const vipAction = u.is_vip ?
            `<button class="cyber-btn small danger" onclick="toggleVip(${u.id}, 'cancel')">取消VIP</button>` :
            `<button class="cyber-btn small" onclick="toggleVip(${u.id}, 'activate')">开通VIP</button>`;
        return `<tr>
            <td>${u.id}</td>
            <td>${u.username}</td>
            <td>${u.email}</td>
            <td>${u.role === 'admin' ? '管理员' : '用户'}</td>
            <td>${vipStatus} ${u.vip_expire_date ? '(至 '+u.vip_expire_date+')' : ''}</td>
            <td>${u.free_predictions_used}</td>
            <td>${vipAction}</td>
        </tr>`;
    }).join('');
}

async function toggleVip(userId, action) {
    const reason = prompt('操作原因（可选）：');
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', action);
    if (reason) formData.append('reason', reason);
    const resp = await fetch('/api/api_admin.php?action=toggle_vip', {
        method: 'POST',
        body: formData
    });
    const result = await resp.json();
    if (result.success) {
        alert('操作成功');
        loadUsers();
    } else {
        alert(result.message || '操作失败');
    }
}
</script>