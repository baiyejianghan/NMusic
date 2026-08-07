<?php
// 歌曲列表接口：扫描 music 文件夹，返回歌曲元数据
require_once __DIR__ . '/common.php';
header('Content-Type: application/json; charset=utf-8');

$group = $_GET['group'] ?? 'all'; // all / album / artist / new
$limit = isset($_GET['limit']) ? max(0, intval($_GET['limit'])) : 0;

$files = listAudioFiles();
// 缓存键：基于文件列表 + 修改时间，文件没变直接复用缓存的元数据（避免每次重读 ID3 / 扫目录）
$cacheDir = MUSIC_ROOT . '/.cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . '/catalog.json';
$sig = '';
foreach ($files as $f) {
    $sig .= $f . ':' . @filemtime(MUSIC_ROOT . '/' . $f) . "\n";
}
$sig = md5($sig);

$cached = null;
if (is_file($cacheFile)) {
    $j = @json_decode(@file_get_contents($cacheFile), true);
    if (is_array($j) && isset($j['sig']) && $j['sig'] === $sig && isset($j['songs']) && is_array($j['songs'])) {
        $cached = $j['songs'];
    }
}

if ($cached !== null) {
    $songs = $cached;
} else {
    $songs = [];
    $id = 0;
    $coverDir = MUSIC_ROOT . '/' . COVER_DIR_NAME;
    $lyricDir = MUSIC_ROOT . '/' . LYRIC_DIR_NAME;
    $covers = is_dir($coverDir) ? listFilesByExt($coverDir, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp']) : [];
    $lyrics = is_dir($lyricDir) ? listFilesByExt($lyricDir, ['lrc']) : [];

    foreach ($files as $f) {
        $base = stripExt($f);
        $meta = null;
        if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'mp3') {
            $meta = readMp3Meta(MUSIC_ROOT . '/' . $f);
        }
        $title = $meta['title'] ?? $base;
        $artist = $meta['artist'] ?? null;
        $album  = $meta['album']  ?? null;

        // 从文件名拆分「歌名-歌手」或「歌手 - 歌名」（仅当 ID3 无信息时）
        if ($meta['title'] === null || $artist === null) {
            $parts = nameParts($base);
            if (count($parts) === 2) {
                // 优先「歌名-歌手」：第一段为歌名、第二段为歌手
                if ($meta['title'] === null && $artist === null) {
                    $title  = $parts[0];
                    $artist = $parts[1];
                } else {
                    $missing = $meta['title'] === null ? $parts[0] : $parts[1];
                    if ($meta['title'] === null) $title  = $missing;
                    if ($artist === null)        $artist = $missing;
                }
            }
        }
        if ($artist === null) $artist = '未知艺术家';
        if ($album === null)  $album  = '未知专辑';

        $songs[] = [
            'id'       => $id++,
            'title'    => $title,
            'artist'   => $artist,
            'album'    => $album,
            'file'     => $f,
            'base'     => $base,
            'hasLyric' => findLyricFile($base, $lyrics) !== null,
            'hasCover' => findCoverFile($base, $covers) !== null,
            'duration' => getAudioDuration($f),
            'mtime'    => filemtime(MUSIC_ROOT . '/' . $f),
        ];
    }
    @file_put_contents($cacheFile, json_encode(['sig' => $sig, 'songs' => $songs], JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// 排序：新歌按修改时间降序
if ($group === 'new') {
    usort($songs, function ($a, $b) { return $b['mtime'] <=> $a['mtime']; });
} else {
    usort($songs, function ($a, $b) { return strcmp($a['title'], $b['title']); });
}

$total = count($files);
if ($limit > 0) {
    $songs = array_slice($songs, 0, $limit);
}

// HTTP 缓存：文件没变时返回 304 Not Modified，浏览器复用缓存（省掉每次 212KB 传输）
$etag = '"' . $sig . '-' . $group . '-' . $limit . '"';
header('Cache-Control: no-cache');
header('ETag: ' . $etag);
$inm = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '');
if (strncmp($inm, 'W/', 2) === 0) $inm = trim(substr($inm, 2)); // 兼容中间层添加的弱校验 W/ 前缀
if ($inm !== '' && $inm === $etag) {
    http_response_code(304);
    exit;
}

// gzip 压缩（若客户端支持且未压缩），大幅减小 JSON 传输体积
$zoc = strtolower(trim((string)ini_get('zlib.output_compression')));
if (function_exists('ob_gzhandler') && ($zoc === '' || $zoc === '0' || $zoc === 'off')) {
    ob_start('ob_gzhandler');
}

echo json_encode(['ok' => true, 'data' => ['songs' => $songs, 'total' => $total]], JSON_UNESCAPED_UNICODE);
