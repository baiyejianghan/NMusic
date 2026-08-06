<?php
// 歌曲列表接口：扫描 music 文件夹，返回歌曲元数据
require_once __DIR__ . '/common.php';
header('Content-Type: application/json; charset=utf-8');

$group = $_GET['group'] ?? 'all'; // all / album / artist / new
$limit = isset($_GET['limit']) ? max(0, intval($_GET['limit'])) : 0;

$files = listAudioFiles();
$songs = [];
$id = 0;

foreach ($files as $f) {
    // 快速模式：提前返回前 limit 首，避免完整扫描拖慢首屏
    if ($limit > 0 && count($songs) >= $limit) break;
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
        'hasLyric' => findLyricFile($base) !== null,
        'hasCover' => findCoverFile($base) !== null,
        'duration' => getAudioDuration($f),
        'mtime'    => filemtime(MUSIC_ROOT . '/' . $f),
    ];
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

echo json_encode(['ok' => true, 'data' => ['songs' => $songs, 'total' => $total]], JSON_UNESCAPED_UNICODE);
