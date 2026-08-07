// ===== 视图渲染 =====

// 首页轮播图配置（5 张）：image 填图片地址，link 填跳转链接
// 占位时留空会显示色块，替换成真实图片即可
const BANNERS = [
  { image: '', link: '', title: '横幅 1' },
  { image: '', link: '', title: '横幅 2' },
  { image: '', link: '', title: '横幅 3' },
  { image: '', link: '', title: '横幅 4' },
  { image: '', link: '', title: '横幅 5' },
];

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
      ? `<img src="${API.coverUrl(song.base)}" loading="lazy" decoding="async" alt="">`
      : ICON('music');
    const sub = song.sub || song.artist || '';
    const liked = App.isLiked(song.file);
    return `
    <div class="card" data-file="${fmt.esc(song.file)}" data-base="${fmt.esc(song.base)}">
      <div class="card-cover">${cover}
        <button class="card-like like-btn ${liked ? 'liked' : ''}" data-file="${fmt.esc(song.file)}" title="${liked ? '取消喜欢' : '喜欢'}" onclick="event.stopPropagation()"><svg class="ic" viewBox="0 0 24 24"><use href="#${liked ? 'i-heart-fill' : 'i-heart'}"/></svg></button>
        <div class="card-play">${ICON('play')}</div>
      </div>
      <div class="card-title">${fmt.esc(song.title)}</div>
      <div class="card-sub">${fmt.esc(sub)}</div>
    </div>`;
  },

  trackRow(song) {
    const cover = song.hasCover
      ? `<img src="${API.coverUrl(song.base)}" loading="lazy" decoding="async" alt="">`
      : ICON('music');
    const dur = song.duration ? fmt.time(song.duration) : '—';
    const sub = [song.artist, song.album].filter(x => x && x !== '未知艺术家' && x !== '未知专辑').join(' · ');
    const liked = App.isLiked(song.file);
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
        <button class="t-dl" data-file="${fmt.esc(song.file)}" title="下载" onclick="event.stopPropagation(); App.downloadSong('${fmt.esc(song.file).replace(/'/g, "\\'")}')">${ICON('download')}</button>
        <button class="t-like like-btn ${liked ? 'liked' : ''}" data-file="${fmt.esc(song.file)}" title="${liked ? '取消喜欢' : '喜欢'}" onclick="event.stopPropagation()"><svg class="ic" viewBox="0 0 24 24"><use href="#${liked ? 'i-heart-fill' : 'i-heart'}"/></svg></button>
      </div>
    </div>`;
  },

  _afterRender() {
    // 绑定卡片/列表点击 → 播放（同一视图内按 DOM 顺序组成队列）
    const items = Array.from(document.querySelectorAll('#content .card[data-file], #content .track[data-file], #content .hero[data-file]'));
    const songs = items.map(n => App.getSongByFile(n.dataset.file)).filter(Boolean);
    items.forEach(el => {
      el.addEventListener('click', () => {
        const i = items.indexOf(el);
        App.playSongs(songs, i);
      });
    });

    // 绑定喜欢按钮
    document.querySelectorAll('#content .like-btn[data-file]').forEach(btn => {
      btn.onclick = (e) => {
        e.stopPropagation();
        const song = App.getSongByFile(btn.dataset.file);
        if (!song) return;
        App.toggleLike(song);
        // 若在「我喜欢」页取消喜欢，直接移除该行
        if (App.currentView === 'likes' && !App.isLiked(song.file)) {
          const row = btn.closest('.track, .card');
          if (row) row.remove();
          const remain = document.querySelectorAll('#content .track, #content .card').length;
          if (!remain) Views.likes();
        }
      };
    });
  },

  async home() {
    const songs = App.allSongs;
    const withCover = songs.filter(s => s.hasCover);
    const cover = withCover.length ? withCover[Math.floor(Math.random() * withCover.length)] : null;
    const hero = cover
      ? `<div class="hero" data-file="${fmt.esc(cover.file)}" style="background-image:url('${API.coverUrl(cover.base)}')">
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

    const slides = BANNERS.map((b, i) => {
      const body = b.image
        ? `<img src="${fmt.esc(b.image)}" alt="${fmt.esc(b.title)}">`
        : `<div class="banner-ph" style="--ph:${i % 5}"><span>${i + 1}</span></div>`;
      const inner = `<div class="banner-body">${body}<div class="banner-title">${fmt.esc(b.title)}</div></div>`;
      return b.link
        ? `<a class="banner-slide" href="${fmt.esc(b.link)}" target="_blank" rel="noopener">${inner}</a>`
        : `<div class="banner-slide">${inner}</div>`;
    }).join('');
    const dots = BANNERS.map((_, i) => `<button class="banner-dot${i === 0 ? ' active' : ''}" data-i="${i}"></button>`).join('');
    const banner = BANNERS.length ? `
      <div class="banner" id="homeBanner">
        <div class="banner-track">${slides}</div>
        <div class="banner-dots">${dots}</div>
      </div>` : '';

    const recentIds = App.getRecent().filter(id => id < songs.length);
    const recentCards = recentIds.slice(0, 10).map(id => this.card(songs[id])).join('');
    const heroCard = `<div class="home-hero">${hero}${banner}</div>` + (recentCards ? `<div class="section"><div class="section-head"><div class="section-title">最近播放</div><div class="section-more" data-view="recent">显示全部</div></div><div class="hscroll">${recentCards}</div></div>` : '');

    // 热门歌曲（保留）：前 12 首
    const hotSection = songs.length ? `
      <div class="section"><div class="section-head"><div class="section-title">热门歌曲</div><div class="section-more" data-view="songs">显示全部</div></div>
      <div class="hscroll">${songs.slice(0, 10).map(s => this.card(s)).join('')}</div></div>` : '';

    // 喜欢的歌曲（无则隐藏该栏）
    const likedSongs = App.getLikes().map(f => App.getSongByFile(f)).filter(Boolean).slice(0, 10);
    const likedSection = likedSongs.length ? `
      <div class="section"><div class="section-head"><div class="section-title">喜欢的歌曲</div><div class="section-more" data-view="likes">显示全部</div></div>
      <div class="hscroll">${likedSongs.map(s => this.card(s)).join('')}</div></div>` : '';

    // 猜你喜欢：随机抽取 12 首
    const guessList = songs.slice().sort(() => Math.random() - 0.5).slice(0, 10);
    const guessSection = songs.length ? `
      <div class="section"><div class="section-head"><div class="section-title">猜你喜欢</div></div>
      <div class="hscroll">${guessList.map(s => this.card(s)).join('')}</div></div>` : '';

    // 最新新歌：文库末尾最近添加的一些
    const newSection = songs.length ? `
      <div class="section"><div class="section-head"><div class="section-title">最新新歌</div><div class="section-more" data-view="songs">显示全部</div></div>
      <div class="hscroll">${songs.slice(-10).reverse().map(s => this.card(s)).join('')}</div></div>` : '';

    this.content.innerHTML = heroCard + `
      ${hotSection}
      ${likedSection}
      ${guessSection}
      ${newSection}`;
    this._afterRender();
    this._initBanner();
    document.querySelectorAll('.section-more[data-view]').forEach(el => el.addEventListener('click', () => App.go(el.dataset.view)));
  },

  // 首页轮播
  _initBanner() {
    const box = document.getElementById('homeBanner');
    if (!box || BANNERS.length < 2) return;
    const track = box.querySelector('.banner-track');
    const dots = box.querySelectorAll('.banner-dot');
    let cur = 0, timer;
    const go = (i) => {
      cur = (i + BANNERS.length) % BANNERS.length;
      track.style.transform = `translateX(${-cur * 100}%)`;
      dots.forEach((d, j) => d.classList.toggle('active', j === cur));
    };
    dots.forEach(d => d.addEventListener('click', () => go(parseInt(d.dataset.i, 10))));
    const play = () => { clearInterval(timer); timer = setInterval(() => go(cur + 1), 4000); };
    box.addEventListener('mouseenter', () => clearInterval(timer));
    box.addEventListener('mouseleave', play);
    // 触屏左右滑动
    let sx = 0;
    box.addEventListener('touchstart', (e) => { sx = e.touches[0].clientX; clearInterval(timer); }, { passive: true });
    box.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 40) go(cur + (dx < 0 ? 1 : -1));
      play();
    }, { passive: true });
    play();
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
        <button class="btn-dl-all" onclick="App.downloadAll()">${ICON('download')} 批量下载全部</button>
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
      <div class="album-bigcover">${first.hasCover ? `<img src="${API.coverUrl(first.base)}">` : ICON('music')}</div>
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
      ${rows ? `<div class="tracklist"><div class="tracklist-head"><div></div><div>标题</div><div>时长</div></div>${rows}</div>` : `<div class="empty"><div class="e-icon">${ICON('headphone')}</div>还没有播放记录</div>`}
      ${this.loadingMore()}`;
    this._afterRender();
  },

  async likes() {
    const files = App.getLikes();
    const list = files.map(f => App.getSongByFile(f)).filter(Boolean);
    const rows = list.map((s, i) => this.trackRow({ ...s, idx: i + 1 })).join('');
    this.content.innerHTML = `
      <div class="section-title" style="font-size:26px;margin-bottom:18px">我喜欢 <span style="font-size:14px;color:var(--text3);font-weight:400">${list.length} 首</span></div>
      ${rows ? `<div class="tracklist"><div class="tracklist-head"><div></div><div>标题</div><div>时长</div></div>${rows}</div>` : `<div class="empty"><div class="e-icon">${ICON('heart')}</div>还没有喜欢的歌曲<br><span style="font-size:13px;color:var(--text3)">点击歌曲旁的<span style="color:var(--accent)">心形</span>收藏</span></div>`}
      ${this.loadingMore()}`;
    this._afterRender();
  },

  searchResult(songs, keyword) {
    if (!keyword.trim()) {
      this.content.innerHTML = `<div class="empty"><div class="e-icon">${ICON('search')}</div>输入关键词搜索歌曲</div>`;
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
      ${rows ? `<div class="tracklist"><div class="tracklist-head"><div></div><div>标题</div><div>时长</div></div>${rows}</div>` : `<div class="empty"><div class="e-icon">${ICON('sad')}</div>未找到相关歌曲</div>`}`;
    this._afterRender();
  },

  empty() {
    this.content.innerHTML = `<div class="empty"><div class="e-icon">${ICON('music')}</div><h3>音乐库为空</h3><p style="margin-top:8px">请将音频文件放入网站的 <code>music/</code> 文件夹<br>歌词放入 <code>music/歌词/</code>，封面放入 <code>music/封面/</code></p></div>`;
  },
};
