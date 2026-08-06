// ===== 视图渲染 =====
const Views = {
  content: document.getElementById('content'),
  cache: {},

  loading() {
    this.content.innerHTML = '<div class="loading"><div class="spin"></div><p>加载中…</p></div>';
  },

  // 后台仍在补全全部歌曲时，在列表末尾显示加载提示
  loadingMore() {
    return App._loadingFull
      ? '<div class="loading-more"><div class="spin"></div><span>正在加载全部歌曲…</span></div>'
      : '';
  },

  // 卡片（专辑）
  card(song) {
    const cover = song.hasCover
      ? `<img src="${API.coverUrl(song.base)}" loading="lazy" alt="">`
      : '♪';
    const sub = song.sub || song.artist || '';
    return `
    <div class="card" data-file="${fmt.esc(song.file)}" data-base="${fmt.esc(song.base)}">
      <div class="card-cover">${cover}
        <div class="card-play">▶</div>
      </div>
      <div class="card-title">${fmt.esc(song.title)}</div>
      <div class="card-sub">${fmt.esc(sub)}</div>
    </div>`;
  },

  trackRow(song) {
    const cover = song.hasCover
      ? `<img src="${API.coverUrl(song.base)}" loading="lazy" alt="">`
      : '♪';
    const dur = song.duration ? fmt.time(song.duration) : '—';
    const sub = [song.artist, song.album].filter(x => x && x !== '未知艺术家' && x !== '未知专辑').join(' · ');
    return `
    <div class="track" data-file="${fmt.esc(song.file)}" data-base="${fmt.esc(song.base)}">
      <div class="t-idx">${song.idx}</div>
      <div class="track-main">
        <div class="t-track-cover">${cover}</div>
        <div>
          <div class="t-title">${fmt.esc(song.title)}</div>
          <div class="t-sub">${fmt.esc(sub)}</div>
        </div>
      </div>
      <div class="t-right">
        <span class="t-dur">${dur}</span>
        <button class="t-dl" data-file="${fmt.esc(song.file)}" title="下载" onclick="event.stopPropagation(); App.downloadSong('${fmt.esc(song.file).replace(/'/g, "\\'")}')">⬇</button>
      </div>
    </div>`;
  },

  _afterRender() {
    // 绑定卡片/列表点击 → 播放（同一视图内按 DOM 顺序组成队列）
    const items = Array.from(document.querySelectorAll('#content .card[data-file], #content .track[data-file]'));
    const songs = items.map(n => App.getSongByFile(n.dataset.file)).filter(Boolean);
    items.forEach(el => {
      el.addEventListener('click', () => {
        const i = items.indexOf(el);
        App.playSongs(songs, i);
      });
    });
  },

  async home() {
    const songs = App.allSongs;
    const cover = songs.find(s => s.hasCover);
    const hero = cover
      ? `<div class="hero" style="background-image:url('${API.coverUrl(cover.base)}')">
          <div class="hero-content">
            <div class="hero-kicker">推荐</div>
            <div class="hero-title">${fmt.esc(cover.title)}</div>
            <div class="hero-sub">${fmt.esc(cover.artist)} · ${fmt.esc(cover.album)}</div>
          </div>
        </div>`
      : `<div class="hero" style="background:linear-gradient(135deg,#1a1a22,#3a1220)">
          <div class="hero-content">
            <div class="hero-kicker">NMusic</div>
            <div class="hero-title">欢迎回来</div>
            <div class="hero-sub">共 ${songs.length} 首歌曲</div>
          </div>
        </div>`;

    const recentIds = App.getRecent().filter(id => id < songs.length);
    const recentCards = recentIds.slice(0, 10).map(id => this.card(songs[id])).join('');
    const heroCard = hero + (recentCards ? `<div class="section"><div class="section-head"><div class="section-title">最近播放</div><div class="section-more" data-view="recent">显示全部</div></div><div class="hscroll">${recentCards}</div></div>` : '');
    const allCards = songs.map(s => this.card(s)).join('');
    const trackRows = songs.map((s, i) => this.trackRow({ ...s, idx: i + 1 })).join('');

    this.content.innerHTML = heroCard + `
      <div class="section"><div class="section-head"><div class="section-title">热门歌曲</div><div class="section-more" data-view="songs">显示全部</div></div>
      <div class="hscroll">${songs.slice(0, 12).map(s => this.card(s)).join('')}</div></div>
      <div class="section"><div class="section-title" style="margin-bottom:14px">全部歌曲</div><div class="tracklist">
      <div class="tracklist-head"><div></div><div>标题</div><div>时长</div></div>${trackRows}</div></div>
      ${this.loadingMore()}`;
    void allCards;
    this._afterRender();
    document.querySelectorAll('.section-more[data-view]').forEach(el => el.addEventListener('click', () => App.go(el.dataset.view)));
  },

  async newSongs() {
    const songs = [...App.allSongs].sort((a, b) => b.mtime - a.mtime);
    const rows = songs.map((s, i) => this.trackRow({ ...s, idx: i + 1 })).join('');
    this.content.innerHTML = `
      <div class="section-title" style="font-size:26px;margin-bottom:18px">新歌</div>
      <div class="hscroll" style="margin-bottom:24px">${songs.slice(0, 12).map(s => this.card(s)).join('')}</div>
      <div class="tracklist"><div class="tracklist-head"><div></div><div>标题</div><div>时长</div></div>${rows}</div>
      ${this.loadingMore()}`;
    this._afterRender();
  },

  async songs() {
    const songs = App.allSongs;
    const rows = songs.map((s, i) => this.trackRow({ ...s, idx: i + 1 })).join('');
    this.content.innerHTML = `
      <div class="view-head">
        <div class="section-title">全部歌曲 <span style="font-size:14px;color:var(--text3);font-weight:400">${songs.length} 首</span></div>
        <button class="btn-dl-all" onclick="App.downloadAll()">⬇ 批量下载全部</button>
      </div>
      <div class="tracklist"><div class="tracklist-head"><div></div><div>标题</div><div style="text-align:right">时长</div></div>${rows}</div>
      ${this.loadingMore()}`;
    this._afterRender();
  },

  async albums() {
    const songs = App.allSongs;
    const map = {};
    songs.forEach(s => { (map[s.album] = map[s.album] || []).push(s); });
    const names = Object.keys(map).sort();
    const first = map[names[0]] && map[names[0]][0];
    const hero = first ? `<div class="album-hero">
      <div class="album-bigcover">${first.hasCover ? `<img src="${API.coverUrl(first.base)}">` : '♪'}</div>
      <div><div class="album-kicker">专辑</div><div class="album-name">${fmt.esc(first.album)}</div><div class="album-artist">${fmt.esc(first.artist)} · ${map[names[0]].length} 首歌曲</div></div>
    </div>` : '';
    const cards = names.map(n => this.card(map[n][0])).join('');
    this.content.innerHTML = hero + `
      <div class="section-title" style="font-size:26px;margin-bottom:18px">专辑</div>
      <div class="grid">${cards}</div>`;
    this._afterRender();
  },

  async artists() {
    const songs = App.allSongs;
    const map = {};
    songs.forEach(s => { (map[s.artist] = map[s.artist] || []).push(s); });
    const names = Object.keys(map).sort();
    const cards = names.map(n => {
      const s = map[n][0];
      return this.card({ ...s, title: n, sub: `${map[n].length} 首歌曲` });
    }).join('');
    this.content.innerHTML = `
      <div class="section-title" style="font-size:26px;margin-bottom:18px">艺术家</div>
      <div class="grid">${cards}</div>
      ${this.loadingMore()}`;
    this._afterRender();
  },

  async recent() {
    const songs = App.allSongs;
    const ids = App.getRecent().filter(id => id < songs.length);
    const list = ids.map(id => songs[id]);
    const rows = list.map((s, i) => this.trackRow({ ...s, idx: i + 1 })).join('');
    this.content.innerHTML = `
      <div class="section-title" style="font-size:26px;margin-bottom:18px">最近播放</div>
      ${rows ? `<div class="tracklist"><div class="tracklist-head"><div></div><div>标题</div><div>时长</div></div>${rows}</div>` : '<div class="empty"><div class="e-icon">🎧</div>还没有播放记录</div>'}
      ${this.loadingMore()}`;
    this._afterRender();
  },

  searchResult(songs, keyword) {
    if (!keyword.trim()) {
      this.content.innerHTML = '<div class="empty"><div class="e-icon">🔍</div>输入关键词搜索歌曲</div>';
      return;
    }
    const k = keyword.toLowerCase();
    const terms = k.split(/\s+/).filter(Boolean);
    const fuzzy = (term, hay) => {
      if (hay.includes(term)) return true;
      let i = 0;
      for (const ch of hay) { if (ch === term[i]) i++; if (i === term.length) return true; }
      return false;
    };
    const matched = songs.filter(s => {
      const hay = `${s.title} ${s.artist} ${s.album}`.toLowerCase();
      return terms.every(t => fuzzy(t, hay));
    });
    const rows = matched.map((s, i) => this.trackRow({ ...s, idx: i + 1 })).join('');
    this.content.innerHTML = `
      <div class="section-title" style="font-size:26px;margin-bottom:18px">搜索「${fmt.esc(keyword)}」 <span style="font-size:14px;color:var(--text3);font-weight:400">${matched.length} 个结果</span></div>
      ${rows ? `<div class="tracklist"><div class="tracklist-head"><div></div><div>标题</div><div>时长</div></div>${rows}</div>` : '<div class="empty"><div class="e-icon">😕</div>未找到相关歌曲</div>'}`;
    this._afterRender();
  },

  empty() {
    this.content.innerHTML = `<div class="empty"><div class="e-icon">🎵</div><h3>音乐库为空</h3><p style="margin-top:8px">请将音频文件放入网站的 <code>music/</code> 文件夹<br>歌词放入 <code>music/歌词/</code>，封面放入 <code>music/封面/</code></p></div>`;
  },
};
