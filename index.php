<?php // 个人音乐网站 - 入口（SPA）
require_once __DIR__ . '/config.php';
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>NMusic</title>
<meta name="theme-color" content="#0a0a0c">
<link rel="icon" href="assets/logo.png">
<link rel="stylesheet" href="assets/css/style.css?v=65">
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
  <symbol id="i-home" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></symbol>
  <symbol id="i-new" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></symbol>
  <symbol id="i-album" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></symbol>
  <symbol id="i-mic" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></symbol>
  <symbol id="i-headphone" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></symbol>
  <symbol id="i-shuffle" viewBox="0 0 24 24"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></symbol>
  <symbol id="i-chev-left" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></symbol>
  <symbol id="i-chev-right" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></symbol>
  <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></symbol>
  <symbol id="i-music" viewBox="0 0 24 24"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></symbol>
  <symbol id="i-play" viewBox="0 0 24 24"><polygon points="6 4 18 12 6 20 6 4"/></symbol>
  <symbol id="i-pause" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></symbol>
  <symbol id="i-prev" viewBox="0 0 24 24"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/></symbol>
  <symbol id="i-next" viewBox="0 0 24 24"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/></symbol>
  <symbol id="i-repeat" viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></symbol>
  <symbol id="i-repeat-one" viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/><line x1="12" y1="11" x2="12" y2="16"/></symbol>
  <symbol id="i-lyric" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></symbol>
  <symbol id="i-volume" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></symbol>
  <symbol id="i-volume-low" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></symbol>
  <symbol id="i-volume-mute" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></symbol>
  <symbol id="i-download" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></symbol>
  <symbol id="i-share" viewBox="0 0 24 24"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="i-sad" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></symbol>
  <symbol id="i-smile" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></symbol>
  <symbol id="i-heart-fill" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></symbol>
  <symbol id="i-close" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
</svg>
<div class="loading-bar" id="loadingBar" style="display:none"><div class="loading-bar-fill"></div></div>
<div class="app">
  <!-- ===== 侧边栏 ===== -->
  <aside class="sidebar" id="sidebar">
    <div class="logo">
      <img src="assets/logo.png" alt="NMusic">
      <span>NMusic</span>
    </div>
    <nav class="nav" id="nav">
      <a class="nav-item active" data-view="home"><svg class="ic" viewBox="0 0 24 24"><use href="#i-home"/></svg>首页</a>
      <a class="nav-item" data-view="new"><svg class="ic" viewBox="0 0 24 24"><use href="#i-new"/></svg>新歌</a>
      <a class="nav-item" data-view="albums"><svg class="ic" viewBox="0 0 24 24"><use href="#i-album"/></svg>专辑</a>
      <a class="nav-item" data-view="artists"><svg class="ic" viewBox="0 0 24 24"><use href="#i-mic"/></svg>艺术家</a>
    </nav>
    <div class="nav-group">
      <div class="nav-group-title">资料库</div>
      <a class="nav-item" data-view="songs"><svg class="ic" viewBox="0 0 24 24"><use href="#i-headphone"/></svg>全部歌曲</a>
      <a class="nav-item" data-view="likes"><svg class="ic" viewBox="0 0 24 24"><use href="#i-heart"/></svg>我喜欢</a>
      <a class="nav-item" data-action="shuffle"><svg class="ic" viewBox="0 0 24 24"><use href="#i-shuffle"/></svg>随机播放</a>
      <a class="nav-item" data-view="recent"><svg class="ic" viewBox="0 0 24 24"><use href="#i-clock"/></svg>最近播放</a>
    </div>
    <div class="sidebar-bottom">
      <div class="user-box"><svg class="ic" viewBox="0 0 24 24"><use href="#i-smile"/></svg> 欢迎使用 NMusic</div>
      <div class="sidebar-links">
        <a class="side-link" href="<?= htmlspecialchars(LINK_SPONSOR) ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
          赞助支持
        </a>
        <a class="side-link" href="<?= htmlspecialchars(LINK_GITHUB) ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 .5C5.6.5.5 5.6.5 12c0 5.1 3.3 9.4 7.9 10.9.6.1.8-.3.8-.6v-2c-3.2.7-3.9-1.5-3.9-1.5-.5-1.3-1.3-1.7-1.3-1.7-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.7 1.3 3.4 1 .1-.8.4-1.3.7-1.6-2.6-.3-5.3-1.3-5.3-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17.3 4.6 18.3 5 18.3 5c.6 1.6.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.4-2.7 5.4-5.3 5.7.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6 4.6-1.5 7.9-5.8 7.9-10.9C23.5 5.6 18.4.5 12 .5z"/></svg>
          GitHub
        </a>
      </div>
    </div>
  </aside>
  <div class="sidebar-mask" id="sidebarMask"></div>
  <main class="main">
    <header class="topbar">
      <div class="tb-left">
        <button class="icon-btn tb-menu" id="btnMenu" title="菜单"><svg class="ic" viewBox="0 0 24 24"><use href="#i-menu"/></svg></button>
        <button class="icon-btn" id="btnBack" title="后退"><svg class="ic" viewBox="0 0 24 24"><use href="#i-chev-left"/></svg></button>
        <button class="icon-btn" id="btnFwd" title="前进"><svg class="ic" viewBox="0 0 24 24"><use href="#i-chev-right"/></svg></button>
      </div>
      <div class="search-wrap">
        <span class="search-icon"><svg class="ic" viewBox="0 0 24 24"><use href="#i-search"/></svg></span>
        <input type="text" id="searchInput" placeholder="搜索歌曲、艺术家、专辑" autocomplete="off">
      </div>
      <div class="tb-right">
        <a class="tb-link" href="<?= htmlspecialchars(LINK_SPONSOR) ?>" target="_blank" rel="noopener" title="赞助支持">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </a>
        <a class="tb-link" href="<?= htmlspecialchars(LINK_GITHUB) ?>" target="_blank" rel="noopener" title="GitHub">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 .5C5.6.5.5 5.6.5 12c0 5.1 3.3 9.4 7.9 10.9.6.1.8-.3.8-.6v-2c-3.2.7-3.9-1.5-3.9-1.5-.5-1.3-1.3-1.7-1.3-1.7-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.7 1.3 3.4 1 .1-.8.4-1.3.7-1.6-2.6-.3-5.3-1.3-5.3-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17.3 4.6 18.3 5 18.3 5c.6 1.6.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.4-2.7 5.4-5.3 5.7.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6 4.6-1.5 7.9-5.8 7.9-10.9C23.5 5.6 18.4.5 12 .5z"/></svg>
        </a>
      </div>
    </header>
    <div class="content" id="content">
      <!-- 视图渲染容器 -->
    </div>
    <footer class="site-footer">© NMusic · 百叶江寒所有权 · 如涉侵权请联系删除</footer>
  </main>
  <!-- ===== 迷你播放条 ===== -->
  <footer class="mini-player" id="miniPlayer">
    <div class="mp-left" id="mpLeft">
      <div class="mp-cover" id="mpCover"><svg class="ic big" viewBox="0 0 24 24"><use href="#i-music"/></svg></div>
      <div class="mp-info">
        <div class="mp-title" id="mpTitle">未在播放</div>
        <div class="mp-artist" id="mpArtist">选择一首歌曲开始播放</div>
      </div>
    </div>
    <div class="mp-center">
      <div class="mp-controls">
        <button class="ctl" id="btnShuffle" title="随机播放"><svg class="ic" viewBox="0 0 24 24"><use href="#i-shuffle"/></svg></button>
        <button class="ctl" id="btnPrev" title="上一首"><svg class="ic" viewBox="0 0 24 24"><use href="#i-prev"/></svg></button>
        <button class="ctl ctl-main" id="btnPlay" title="播放/暂停"><svg class="ic play" viewBox="0 0 24 24"><use href="#i-play"/></svg></button>
        <button class="ctl" id="btnNext" title="下一首"><svg class="ic" viewBox="0 0 24 24"><use href="#i-next"/></svg></button>
        <button class="ctl" id="btnRepeat" title="循环模式"><svg class="ic" viewBox="0 0 24 24"><use href="#i-repeat"/></svg></button>
      </div>
      <div class="progress">
        <span class="time" id="curTime">0:00</span>
        <div class="bar" id="progressBar"><div class="bar-fill" id="progressFill"></div><div class="bar-ghost" id="progressGhost"></div><div class="bar-thumb" id="progressThumb"></div></div>
        <span class="time" id="durTime">0:00</span>
      </div>
    </div>
    <div class="mp-right">
      <button class="ctl" id="btnLyric" title="歌词"><svg class="ic" viewBox="0 0 24 24"><use href="#i-lyric"/></svg></button>
      <button class="ctl" id="btnLike" title="喜欢"><svg class="ic" viewBox="0 0 24 24"><use href="#i-heart"/></svg></button>
      <button class="ctl" id="btnVolume" title="音量"><svg class="ic" viewBox="0 0 24 24"><use href="#i-volume"/></svg></button>
      <div class="volume-slider"><div class="vbar" id="volumeBar"><div class="vbar-fill" id="volumeFill"></div></div></div>
    </div>
  </footer>
  <!-- ===== 沉浸式播放页 ===== -->
  <div class="nowplaying" id="nowPlaying">
    <div class="np-bg" id="npBg"></div>
    <div class="np-inner">
      <div class="np-top">
        <button class="np-close" id="npClose" title="收起">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="np-center-label">
          <div id="npQueueLabel">正在播放</div>
          <div class="np-title" id="npTitle">未在播放</div>
          <div class="np-artist" id="npArtist">—</div>
        </div>
        <button class="np-lyric-toggle" id="npLyricToggle">歌词</button>
      </div>
      <div class="np-body">
        <div class="np-art" id="npArt"><svg class="ic big" viewBox="0 0 24 24"><use href="#i-music"/></svg></div>
        <div class="np-lyric" id="npLyricArea">
          <div class="np-lyric-inner" id="npLyricInner"></div>
        </div>
      </div>
      <div class="np-bottom">
        <div class="np-progress">
          <span id="npCur">0:00</span>
          <div class="bar" id="npProgressBar"><div class="bar-fill" id="npProgressFill"></div><div class="bar-ghost" id="npProgressGhost"></div><div class="bar-thumb" id="npProgressThumb"></div></div>
          <span id="npDur">0:00</span>
        </div>
        <div class="np-controls">
          <button class="ctl" id="npShuffle"><svg class="ic" viewBox="0 0 24 24"><use href="#i-shuffle"/></svg></button>
          <button class="ctl" id="npPrev"><svg class="ic" viewBox="0 0 24 24"><use href="#i-prev"/></svg></button>
          <button class="ctl ctl-main big" id="npPlay"><svg class="ic play" viewBox="0 0 24 24"><use href="#i-play"/></svg></button>
          <button class="ctl" id="npNext"><svg class="ic" viewBox="0 0 24 24"><use href="#i-next"/></svg></button>
          <button class="ctl" id="npRepeat"><svg class="ic" viewBox="0 0 24 24"><use href="#i-repeat"/></svg></button>
        </div>
        <div class="np-actions">
          <button class="ctl" id="npLike" title="喜欢"><svg class="ic" viewBox="0 0 24 24"><use href="#i-heart"/></svg></button>
          <button class="ctl" id="npDownload" title="下载"><svg class="ic" viewBox="0 0 24 24"><use href="#i-download"/></svg></button>
          <button class="ctl" id="npShare" title="分享"><svg class="ic" viewBox="0 0 24 24"><use href="#i-share"/></svg></button>
          <div class="np-volume">
            <button class="ctl" id="npVolumeIcon" title="音量"><svg class="ic" viewBox="0 0 24 24"><use href="#i-volume"/></svg></button>
            <div class="volume-slider"><div class="vbar" id="npVolumeBar"><div class="vbar-fill" id="npVolumeFill"></div></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="assets/js/api.js?v=65"></script>
<script src="assets/js/player.js?v=65"></script>
<script src="assets/js/views.js?v=65"></script>
<script src="assets/js/app.js?v=65"></script>
</body>
</html>
