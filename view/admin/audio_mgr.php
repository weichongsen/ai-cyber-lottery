<div class="page-container" id="audio-mgr-page">
    <h2 class="page-title">🎵 音效文件管理</h2>
    <div class="cyber-panel">
        <h3>上传音效</h3>
        <form id="audio-upload-form" onsubmit="uploadAudio(event)">
            <input type="file" name="audio" accept=".mp3,.wav" required>
            <button type="submit" class="cyber-btn">上传</button>
        </form>
    </div>
    <div class="cyber-panel" style="margin-top:20px;">
        <h3>现有音效文件</h3>
        <ul id="audio-list"></ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadAudioList);

async function loadAudioList() {
    try {
        const resp = await fetch('/api/api_admin.php?action=list_audio');
        const data = await resp.json();
        if (data.success) {
            const list = document.getElementById('audio-list');
            list.innerHTML = data.data.map(f => `<li>${f.name} <audio controls src="${f.path}"></audio></li>`).join('');
        }
    } catch(e) {}
}

async function uploadAudio(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const resp = await fetch('/api/api_admin.php?action=upload_audio', { method: 'POST', body: formData });
    const result = await resp.json();
    alert(result.message || '上传完成');
    loadAudioList();
}
</script>