// ===== API 封装 =====
const API = {
  base: 'api',

  async _get(url) {
    const r = await fetch(url, { credentials: 'same-origin' });
    return r.json();
  },
  async _post(url, data) {
    const body = new URLSearchParams(data);
    const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body, credentials: 'same-origin' });
    return r.json();
  },

  // 歌曲列表
  songs: (group = 'all', limit = 0) => API._get(`${API.base}/songs.php?group=${group}${limit ? '&limit=' + limit : ''}`),

  // 歌词（按歌名 base）
  lyrics: (base) => API._get(`${API.base}/lyrics.php?file=${encodeURIComponent(base)}`),

  // 封面 URL
  coverUrl: (base) => `${API.base}/cover.php?file=${encodeURIComponent(base)}`,

  // 音频流 URL
  streamUrl: (file) => `${API.base}/stream.php?file=${encodeURIComponent(file)}`,

  // 下载 URL
  downloadUrl: (file) => `${API.base}/download.php?file=${encodeURIComponent(file)}`,
  downloadAllUrl: (files) => {
    const p = files && files.length ? `files=${encodeURIComponent(files.join(','))}` : '';
    return `${API.base}/download_all.php${p ? '?' + p : ''}`;
  },
};

// 工具
const fmt = {
  time(s) {
    if (!isFinite(s) || s < 0) s = 0;
    s = Math.floor(s);
    const m = Math.floor(s / 60);
    const sec = s % 60;
    return `${m}:${sec.toString().padStart(2, '0')}`;
  },
  // 从封面提取主色（用于渐变背景）
  dominantColor(img, cb) {
    const c = document.createElement('canvas');
    c.width = c.height = 60;
    const ctx = c.getContext('2d');
    ctx.drawImage(img, 0, 0, 60, 60);
    let data;
    try { data = ctx.getImageData(0, 0, 60, 60).data; } catch (e) { cb([120, 45, 60]); return; }
    let r = 0, g = 0, b = 0, n = 0;
    for (let i = 0; i < data.length; i += 4) {
      if (data[i + 3] < 128) continue;
      r += data[i]; g += data[i + 1]; b += data[i + 2]; n++;
    }
    if (!n) { cb([120, 45, 60]); return; }
    cb([Math.round(r / n), Math.round(g / n), Math.round(b / n)]);
  },
  esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
  },
};
