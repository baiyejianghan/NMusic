// ===== 播放器核心 =====
class Player {
  constructor() {
    this.audio = new Audio();
    this.audio.preload = 'metadata';
    this.queue = [];      // 歌曲数组
    this.index = -1;
    this.mode = 'list';   // list | single | shuffle
    this.lyrics = [];
    this.lyricOffset = 0;
    this._lyricTimer = null;

    this.onChange = null;   // 切歌回调
    this.onTime = null;     // 进度回调
    this.onPlayState = null;
    this.onEnd = null;
    this.onMeta = null;     // 获取到时长回调 (song, duration)
    this.onLyrics = null;   // 歌词加载完成回调

    this._bindEvents();
    this._loadPrefs();
  }

  _bindEvents() {
    this.audio.addEventListener('timeupdate', () => this._emitTime());
    this.audio.addEventListener('loadedmetadata', () => {
      this._emitTime();
      const s = this.current;
      const d = this.audio.duration;
      if (s && isFinite(d) && d > 0 && this.onMeta) this.onMeta(s, d);
    });
    this.audio.addEventListener('durationchange', () => {
      const s = this.current;
      const d = this.audio.duration;
      if (s && isFinite(d) && d > 0 && this.onMeta) this.onMeta(s, d);
    });
    this.audio.addEventListener('ended', () => this.next(true));
    this.audio.addEventListener('play', () => this.onPlayState && this.onPlayState(true));
    this.audio.addEventListener('pause', () => this.onPlayState && this.onPlayState(false));
    this.audio.addEventListener('error', () => {
      this.onPlayState && this.onPlayState(false);
      alert('播放失败：文件不存在或格式不支持');
    });
  }

  _loadPrefs() {
    const v = parseFloat(localStorage.getItem('mw.volume'));
    this.volume = isFinite(v) ? v : 0.8;
    this.audio.volume = this.volume;
    const m = localStorage.getItem('mw.mode');
    if (m) this.mode = m;
  }

  get current() { return this.queue[this.index] || null; }
  get playing() { return !this.audio.paused && !this.audio.ended; }

  // 设置并播放队列
  playList(songs, startIndex = 0) {
    this.queue = songs.slice();
    if (!this.queue.length) return;
    this.playAt(startIndex);
  }

  playAt(i) {
    if (i < 0 || i >= this.queue.length) return;
    this.index = i;
    const s = this.current;
    this.audio.src = API.streamUrl(s.file);
    this.audio.play().catch(() => {});
    this._loadLyrics(s.base);
    this.onChange && this.onChange(s, this.index);
  }

  playPause() {
    if (!this.current) return;
    if (this.audio.paused) this.audio.play().catch(() => {});
    else this.audio.pause();
  }

  next(manual = false) {
    if (!this.queue.length) return;
    if (this.mode === 'single' && manual) { this.audio.currentTime = 0; this.audio.play().catch(() => {}); return; }
    if (this.mode === 'shuffle') {
      if (this.queue.length < 2) { this.audio.currentTime = 0; return; }
      let i;
      do { i = Math.floor(Math.random() * this.queue.length); } while (i === this.index);
      this.playAt(i);
      return;
    }
    const ni = (this.index + 1) % this.queue.length;
    this.playAt(ni);
  }

  prev() {
    if (!this.queue.length) return;
    if (this.audio.currentTime > 3) { this.audio.currentTime = 0; return; }
    const pi = (this.index - 1 + this.queue.length) % this.queue.length;
    this.playAt(pi);
  }

  seek(t) {
    if (!this.current) return;
    this.audio.currentTime = Math.max(0, Math.min(t, this.audio.duration || 0));
    this._emitTime();
  }

  setVolume(v) {
    this.volume = Math.max(0, Math.min(1, v));
    this.audio.volume = this.volume;
    localStorage.setItem('mw.volume', this.volume);
  }

  toggleMode() {
    this.mode = this.mode === 'list' ? 'single' : (this.mode === 'single' ? 'shuffle' : 'list');
    localStorage.setItem('mw.mode', this.mode);
    return this.mode;
  }

  // ---- 歌词 ----
  async _loadLyrics(base) {
    this.lyrics = [];
    this.lyricOffset = 0;
    if (this._lyricTimer) { clearInterval(this._lyricTimer); this._lyricTimer = null; }
    try {
      const r = await API.lyrics(base);
      if (r && r.ok && Array.isArray(r.data.lyrics)) {
        this.lyrics = r.data.lyrics;
        const off = r.data.meta && r.data.meta.offset ? r.data.meta.offset / 1000 : 0;
        this.lyricOffset = off;
        this.lyrics.forEach(l => l.t = l.t + off);
      }
    } catch (e) {}
    if (this.onLyrics) this.onLyrics();
    this._emitTime();
  }

  currentLine() {
    if (!this.lyrics.length) return -1;
    const t = this.audio.currentTime;
    let idx = -1;
    for (let i = 0; i < this.lyrics.length; i++) {
      if (t >= this.lyrics[i].t) idx = i;
      else break;
    }
    return idx;
  }

  _emitTime() {
    this.onTime && this.onTime(this.audio.currentTime, this.audio.duration || 0);
  }
}
