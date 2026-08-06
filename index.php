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
<link rel="stylesheet" href="assets/css/style.css?v=37">
</head>
<body>
<div class="loading-bar" id="loadingBar" style="display:none"><div class="loading-bar-fill"></div></div>
<div class="app">
  <!-- ===== 侧边栏 ===== -->
  <aside class="sidebar" id="sidebar">
    <div class="logo">
      <img src="assets/logo.png" alt="NMusic">
      <span>NMusic</span>
    </div>
    <nav class="nav" id="nav">
      <a class="nav-item active" data-view="home"><span>🎵</span>首页</a>
      <a class="nav-item" data-view="new"><span>🆕</span>新歌</a>
      <a class="nav-item" data-view="albums"><span>💿</span>专辑</a>
      <a class="nav-item" data-view="artists"><span>🎤</span>艺术家</a>
    </nav>
    <div class="nav-group">
      <div class="nav-group-title">资料库</div>
      <a class="nav-item" data-view="songs"><span>🎧</span>全部歌曲</a>
      <a class="nav-item" data-action="shuffle"><span>🔀</span>随机播放</a>
      <a class="nav-item" data-view="recent"><span>🕘</span>最近播放</a>
    </div>
    <div class="sidebar-bottom">
      <div class="user-box">👋 欢迎使用 NMusic</div>
    </div>
  </aside>
  <main class="main">
    <header class="topbar">
      <div class="tb-left">
        <button class="icon-btn" id="btnBack" title="后退">◀</button>
        <button class="icon-btn" id="btnFwd" title="前进">▶</button>
      </div>
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
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
      <div class="mp-cover" id="mpCover"><span>♪</span></div>
      <div class="mp-info">
        <div class="mp-title" id="mpTitle">未在播放</div>
        <div class="mp-artist" id="mpArtist">选择一首歌曲开始播放</div>
      </div>
    </div>
    <div class="mp-center">
      <div class="mp-controls">
        <button class="ctl" id="btnShuffle" title="随机播放">🔀</button>
        <button class="ctl" id="btnPrev" title="上一首">⏮</button>
        <button class="ctl ctl-main" id="btnPlay" title="播放/暂停">▶</button>
        <button class="ctl" id="btnNext" title="下一首">⏭</button>
        <button class="ctl" id="btnRepeat" title="循环模式">🔁</button>
      </div>
      <div class="progress">
        <span class="time" id="curTime">0:00</span>
        <div class="bar" id="progressBar"><div class="bar-fill" id="progressFill"></div><div class="bar-ghost" id="progressGhost"></div><div class="bar-thumb" id="progressThumb"></div></div>
        <span class="time" id="durTime">0:00</span>
      </div>
    </div>
    <div class="mp-right">
      <button class="ctl" id="btnLyric" title="歌词">📃</button>
      <button class="ctl" id="btnVolume" title="音量">🔊</button>
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
        <div class="np-art" id="npArt"><span>♪</span></div>
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
          <button class="ctl" id="npShuffle">🔀</button>
          <button class="ctl" id="npPrev">⏮</button>
          <button class="ctl ctl-main big" id="npPlay">▶</button>
          <button class="ctl" id="npNext">⏭</button>
          <button class="ctl" id="npRepeat">🔁</button>
        </div>
        <div class="np-actions">
          <button class="ctl" id="npDownload" title="下载">⬇</button>
          <button class="ctl" id="npShare" title="分享">↗</button>
          <div class="np-volume">
            <button class="ctl" id="npVolumeIcon" title="音量">🔊</button>
            <div class="volume-slider"><div class="vbar" id="npVolumeBar"><div class="vbar-fill" id="npVolumeFill"></div></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="assets/js/api.js?v=37"></script>
<script src="assets/js/player.js?v=37"></script>
<script src="assets/js/views.js?v=37"></script>
<script src="assets/js/app.js?v=37"></script>
</body>
</html>
