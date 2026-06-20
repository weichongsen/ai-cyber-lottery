/**
 * AJAX 请求封装（基于 fetch）
 * 提供统一的 GET/POST 方法，自动处理 JSON 响应。
 * 前端所有异步请求均通过此模块调用后端 PHP API。
 */

const API = {
    /**
     * 发送 GET 请求
     * @param {string} url API 地址（如 '/api/api_predict.php?action=analysis&lottery_type=dlt'）
     * @param {object} params 查询参数对象（可选，将追加到 URL）
     * @returns {Promise<object>} 解析后的 JSON 数据
     */
    async get(url, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}&${queryString}` : url;
        const response = await fetch(fullUrl, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        return await response.json();
    },

    /**
     * 发送 POST 请求（默认 FormData）
     * @param {string} url
     * @param {FormData|object} data FormData 或普通对象（对象将转为 FormData）
     * @returns {Promise<object>}
     */
    async post(url, data = {}) {
        let body;
        if (data instanceof FormData) {
            body = data;
        } else {
            body = new FormData();
            for (const key in data) {
                body.append(key, data[key]);
            }
        }
        const response = await fetch(url, {
            method: 'POST',
            body: body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        return await response.json();
    },

    /**
     * 发送 JSON POST
     * @param {string} url
     * @param {object} jsonData
     * @returns {Promise<object>}
     */
    async postJSON(url, jsonData) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(jsonData)
        });
        return await response.json();
    }
};

// 示例用法：
// API.get('/api/api_user.php', { action: 'session' }).then(console.log);
// API.post('/api/api_predict.php?action=predict', { lottery_type: 'dlt', predict_type: 'single' });