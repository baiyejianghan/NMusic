// ===== 应用入口 =====
const App = {
  player: null,
  allSongs: [],
  currentView: 'home',

  async init() {
    this.player = new Player();
    this._bindPlayer();
    this._bindUI();
    this._bindKeyboard();

    // 先快速加载部分歌曲渲染首屏，后台再补全全部；期间顶部显示加载条
    this._loadingFull = true;
    this._showLoading(true);
    const quick = await API.songs('all', 12).catch(() => null);
    if (!quick || !quick.data || !quick.data.songs) {
      this._loadingFull = false;
      this._showLoading(false);
      Views.empty();
      return;
    }
    this.allSongs = quick.data.songs;

    if (!this.allSongs.length) {
      this._loadingFull = false;
      this._showLoading(false);
      Views.empty();
      return;
    }
    this.go('home');
    this._bindNav();
    this._bindSearch();

    // 后台加载全部歌曲，完成后刷新当前视图
    API.songs('all').then(full => {
      if (full && full.data && full.data.songs.length > this.allSongs.length) {
        this.allSongs = full.data.songs;
      }
      this._loadingFull = false;
      this._showLoading(false);
      const v = this.currentView;
      if (v && Views[v]) Views[v].call(Views);
    }).catch(() => {
      this._loadingFull = false;
      this._showLoading(false);
      const v = this.currentView;
      if (v && Views[v]) Views[v].call(Views);
    });
  },

  // ---- 顶部加载条 ----
  _showLoading(on) {
    const bar = document.getElementById('loadingBar');
    if (bar) bar.style.display = on ? 'block' : 'none';
  },

  // ---- 数据查询 ----
  getSongByFile(file) { return this.allSongs.find(s => s.file === file); },

  playSongs(songs, idx) {
    this.player.playList(songs, idx);
  },

  playAll(idx = 0) {
    this.player.playList(this.allSongs, idx);
  },

  // ---- 最近播放 ----
  getRecent() {
    try { return JSON.parse(localStorage.getItem('mw.recent')) || []; } catch (e) { return []; }
  },
  pushRecent(id) {
    let r = this.getRecent().filter(x => x !== id);
    r.unshift(id);
    if (r.length > 100) r = r.slice(0, 100);
    localStorage.setItem('mw.recent', JSON.stringify(r));
  },

  // ---- 路由 ----
  go(view) {
    this.currentView = view;
    document.querySelectorAll('.nav-item[data-view]').forEach(el => {
      el.classList.toggle('active', el.dataset.view === view);
    });
    const fn = Views[view];
    if (fn) fn.call(Views);
    this.history = this.history || [];
    this.history.push(view);
    if (this.history.length > 50) this.history.shift();
  },

  // ---- 下载 ----
  downloadSong(file) {
    const a = document.createElement('a');
    a.href = API.downloadUrl(file);
    a.download = file;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  },

  downloadAll() {
    const a = document.createElement('a');
    a.href = API.downloadAllUrl();
    a.download = 'music_library.zip';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  },

  // ---- 绑定 ----
  _bindPlayer() {
    const p = this.player;
    const btnPlay = document.getElementById('btnPlay');
    const npPlay = document.getElementById('npPlay');

    p.onChange = (s, i) => {
      App.pushRecent(i);
      document.getElementById('mpTitle').textContent = s.title;
      document.getElementById('mpArtist').textContent = s.artist;
      const mpC = document.getElementById('mpCover');
      mpC.innerHTML = s.hasCover ? `<img src="${API.coverUrl(s.base)}">` : '♪';
      document.getElementById('npTitle').textContent = s.title;
      document.getElementById('npArtist').textContent = s.artist;
      const npArt = document.getElementById('npArt');
      npArt.innerHTML = s.hasCover ? `<img src="${API.coverUrl(s.base)}">` : '♪';
      // 背景渐变
      App._updateBg(s);
      // 播放中高亮
      document.querySelectorAll('.track').forEach(el => el.classList.toggle('playing', el.dataset.file === s.file));
      // 标记播放按钮
      btnPlay.textContent = '⏸';
      npPlay.textContent = '⏸';
    };

    // 歌词加载完成后渲染（避免异步加载前渲染空数组）
    p.onLyrics = () => {
      App._renderLyrics();
      App._updateLyric(this.player.audio.currentTime);
    };

    p.onTime = (cur, dur) => {
      document.getElementById('curTime').textContent = fmt.time(cur);
      document.getElementById('durTime').textContent = fmt.time(dur);
      document.getElementById('npCur').textContent = fmt.time(cur);
      document.getElementById('npDur').textContent = fmt.time(dur);
      if (!App._barDragging) {
        const pct = dur ? Math.max(0, Math.min(100, cur / dur * 100)) : 0;
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('progressThumb').style.left = pct + '%';
        document.getElementById('npProgressFill').style.width = pct + '%';
        document.getElementById('npProgressThumb').style.left = pct + '%';
      }
      // 歌词高亮
      App._updateLyric(cur);
    };

    // 播放时获取真实时长，回填列表
    p.onMeta = (s, d) => {
      if (s.duration) return;
      s.duration = d;
      document.querySelectorAll('#content .track[data-file]').forEach(el => {
        if (el.dataset.file === s.file) {
          const durEl = el.querySelector('.t-dur');
          if (durEl) durEl.textContent = fmt.time(d);
        }
      });
    };

    p.onPlayState = (playing) => {
      btnPlay.textContent = playing ? '⏸' : '▶';
      npPlay.textContent = playing ? '⏸' : '▶';
    };

    btnPlay.onclick = () => p.playPause();
    npPlay.onclick = () => p.playPause();
    document.getElementById('btnNext').onclick = () => p.next();
    document.getElementById('npNext').onclick = () => p.next();
    document.getElementById('btnPrev').onclick = () => p.prev();
    document.getElementById('npPrev').onclick = () => p.prev();

    // 随机
    const shuffleBtn = document.getElementById('btnShuffle');
    const npShuffle = document.getElementById('npShuffle');
    const repeatBtn = document.getElementById('btnRepeat');
    const npRepeat = document.getElementById('npRepeat');
    const updMode = () => {
      const shuff = p.mode === 'shuffle';
      const single = p.mode === 'single';
      shuffleBtn.classList.toggle('active', shuff);
      npShuffle.classList.toggle('active', shuff);
      repeatBtn.classList.toggle('active', single);
      npRepeat.classList.toggle('active', single);
      repeatBtn.textContent = single ? '🔂' : '🔁';
      npRepeat.textContent = single ? '🔂' : '🔁';
    };
    shuffleBtn.onclick = npShuffle.onclick = () => { p.toggleMode(); updMode(); };
    repeatBtn.onclick = npRepeat.onclick = () => { p.toggleMode(); updMode(); };
    updMode();

    // 进度条拖拽
    App._barDragging = false;
    [['progressBar', 'progressFill', 'progressThumb'], ['npProgressBar', 'npProgressFill', 'npProgressThumb']].forEach(([barId, fillId, thumbId]) => {
      const bar = document.getElementById(barId);
      const fill = document.getElementById(fillId);
      const thumb = document.getElementById(thumbId);
      const set = (e) => {
        const rect = bar.getBoundingClientRect();
        const ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        const dur = p.audio.duration || 0;
        p.seek(ratio * dur);
        fill.style.width = (ratio * 100) + '%';
        thumb.style.left = (ratio * 100) + '%';
      };
      bar.addEventListener('mousedown', (e) => { App._barDragging = true; bar.classList.add('dragging'); set(e); });
      window.addEventListener('mousemove', (e) => { if (App._barDragging) set(e); });
      window.addEventListener('mouseup', () => { App._barDragging = false; bar.classList.remove('dragging'); });
    });

    // 音量
    const vbar = document.getElementById('volumeBar');
    const vfill = document.getElementById('volumeFill');
    const setVol = (e) => {
      const rect = vbar.getBoundingClientRect();
      p.setVolume(Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)));
      vfill.style.width = (p.volume * 100) + '%';
      document.getElementById('btnVolume').textContent = p.volume === 0 ? '🔇' : (p.volume < 0.5 ? '🔉' : '🔊');
    };
    let volDrag = false;
    vbar.addEventListener('mousedown', (e) => { volDrag = true; setVol(e); });
    window.addEventListener('mousemove', (e) => { if (volDrag) setVol(e); });
    window.addEventListener('mouseup', () => { volDrag = false; });
    vfill.style.width = (p.volume * 100) + '%';
    document.getElementById('btnVolume').textContent = p.volume === 0 ? '🔇' : (p.volume < 0.5 ? '🔉' : '🔊');
  },

  _updateLyric(cur) {
    const idx = this.player.currentLine();
    const groups = document.querySelectorAll('.lrc-group');
    let activeEl = null;
    groups.forEach((el, i) => {
      const on = i === idx;
      el.classList.toggle('active', on);
      if (on) activeEl = el;
    });
    if (idx >= 0 && activeEl && idx !== this._lastLyricIdx) {
      this._lastLyricIdx = idx;
      const area = document.getElementById('npLyricArea');
      const ar = area.getBoundingClientRect();
      const er = activeEl.getBoundingClientRect();
      const target = area.scrollTop + (er.top - ar.top) - area.clientHeight / 2 + er.height / 2;
      area.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
    }
  },

  _renderLyrics() {
    const inner = document.getElementById('npLyricInner');
    const lyricArea = document.getElementById('npLyricArea');
    const lyricToggle = document.getElementById('npLyricToggle');
    const lyr = this.player.lyrics;
    this._lastLyricIdx = -1;
    if (!lyr.length) {
      lyricArea.classList.remove('visible');
      lyricToggle.classList.remove('active');
      inner.innerHTML = '';
      return;
    }
    lyricArea.classList.add('visible');
    lyricToggle.classList.add('active');
    inner.innerHTML = lyr.map(l => {
      const texts = (l.texts && l.texts.length) ? l.texts : [l.text];
      return `<div class="lrc-group" data-t="${l.t}">` +
        texts.map(t => `<div class="lrc-line">${fmt.esc(t)}</div>`).join('') +
        `</div>`;
    }).join('');
    inner.querySelectorAll('.lrc-group').forEach(el => {
      el.onclick = () => this.player.seek(parseFloat(el.dataset.t));
    });
    lyricArea.scrollTop = 0;
  },

  _updateBg(song) {
    const bg = document.getElementById('npBg');
    const img = new Image();
    img.crossOrigin = 'anonymous';
    const apply = (rgb) => {
      const [r, g, b] = rgb;
      bg.style.background = `radial-gradient(circle at 25% 30%, rgba(${r},${g},${b},.55), rgba(10,10,12,.95) 75%)`;
    };
    img.onload = () => fmt.dominantColor(img, apply);
    img.onerror = () => apply([120, 45, 60]);
    if (song && song.hasCover) img.src = API.coverUrl(song.base);
    else apply([60, 45, 60]);
  },

  _bindUI() {
    // 打开/关闭沉浸播放页
    const np = document.getElementById('nowPlaying');
    document.getElementById('mpLeft').onclick = () => { if (this.player.current) np.classList.add('open'); };
    document.getElementById('npClose').onclick = () => np.classList.remove('open');

    // 歌词开关
    const lyricArea = document.getElementById('npLyricArea');
    const lyricToggle = document.getElementById('npLyricToggle');
    lyricToggle.onclick = () => {
      const visible = lyricArea.classList.contains('visible');
      lyricArea.classList.toggle('visible', !visible);
      lyricToggle.classList.toggle('active', !visible);
    };
    document.getElementById('btnLyric').onclick = () => {
      np.classList.add('open');
      if (!lyricArea.classList.contains('visible')) lyricToggle.click();
    };

    // 正在播放页：下载 / 分享 / 音量
    document.getElementById('npDownload').onclick = () => {
      const s = this.player.current;
      if (s) this.downloadSong(s.file);
    };
    document.getElementById('npShare').onclick = () => {
      const s = this.player.current;
      if (!s) return;
      const url = API.streamUrl(s.file);
      const text = `${s.title}${s.artist ? ' - ' + s.artist : ''}`;
      if (navigator.share) {
        navigator.share({ title: text, url: window.location.origin + '/' + url }).catch(() => {});
      } else {
        const tmp = document.createElement('textarea');
        tmp.value = text + '\n' + window.location.origin + '/' + url;
        document.body.appendChild(tmp);
        tmp.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(tmp);
        const btn = document.getElementById('npShare');
        btn.textContent = '✓';
        setTimeout(() => { btn.textContent = '↗'; }, 1200);
      }
    };
    const npVolBar = document.getElementById('npVolumeBar');
    const npVolFill = document.getElementById('npVolumeFill');
    const npVolIcon = document.getElementById('npVolumeIcon');
    const setNpVol = (e) => {
      const rect = npVolBar.getBoundingClientRect();
      this.player.setVolume(Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)));
      npVolFill.style.width = (this.player.volume * 100) + '%';
      npVolIcon.textContent = this.player.volume === 0 ? '🔇' : (this.player.volume < 0.5 ? '🔉' : '🔊');
    };
    npVolBar.addEventListener('mousedown', (e) => { e.stopPropagation(); setNpVol(e); App._npVolDrag = true; });
    window.addEventListener('mousemove', (e) => { if (App._npVolDrag) setNpVol(e); });
    window.addEventListener('mouseup', () => { App._npVolDrag = false; });
    npVolFill.style.width = (this.player.volume * 100) + '%';

    // 前进后退
    document.getElementById('btnBack').onclick = () => {
      const h = this.history || [];
      if (h.length > 1) { h.pop(); this.go(h[h.length - 1]); }
    };
    document.getElementById('btnFwd').onclick = () => this.go('home');
  },

  _bindNav() {
    document.querySelectorAll('.nav-item[data-view]').forEach(el => el.onclick = () => this.go(el.dataset.view));
    document.querySelectorAll('.nav-item[data-action="shuffle"]').forEach(el => el.onclick = () => {
      if (this.allSongs.length) this.player.playList(this.allSongs, Math.floor(Math.random() * this.allSongs.length));
    });
  },

  _bindSearch() {
    const input = document.getElementById('searchInput');
    let timer;
    input.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => Views.searchResult(this.allSongs, input.value), 250);
    });
  },

  _bindKeyboard() {
    document.addEventListener('keydown', (e) => {
      const t = e.target;
      if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA')) return;
      if (e.code === 'Space') { e.preventDefault(); this.player.playPause(); }
      else if (e.code === 'ArrowRight') this.player.seek((this.player.audio.currentTime || 0) + 5);
      else if (e.code === 'ArrowLeft') this.player.seek((this.player.audio.currentTime || 0) - 5);
      else if (e.code === 'ArrowUp') { e.preventDefault(); this.player.setVolume(this.player.volume + 0.05); }
      else if (e.code === 'ArrowDown') { e.preventDefault(); this.player.setVolume(this.player.volume - 0.05); }
    });
  },
};

document.addEventListener('DOMContentLoaded', () => App.init());
