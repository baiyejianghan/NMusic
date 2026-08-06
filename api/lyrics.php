<?php
// 歌词接口：解析 .lrc 文件，返回带时间戳的歌词行
require_once __DIR__ . '/common.php';

$base = $_GET['file'] ?? '';
if ($base === '' || !safeFileName($base)) jsonOut(false, '参数错误');

$lrcFile = findLyricFile($base);
if ($lrcFile === null) jsonOut(false, '未找到歌词');

$content = @file_get_contents(MUSIC_ROOT . '/' . LYRIC_DIR_NAME . '/' . $lrcFile);
if ($content === false) jsonOut(false, '歌词读取失败');

// 解析元信息
$meta = [];
if (preg_match('/\[ti:(.*?)\]/', $content, $m)) $meta['ti'] = trim($m[1]);
if (preg_match('/\[ar:(.*?)\]/', $content, $m)) $meta['ar'] = trim($m[1]);
if (preg_match('/\[al:(.*?)\]/', $content, $m)) $meta['al'] = trim($m[1]);
if (preg_match('/\[by:(.*?)\]/', $content, $m)) $meta['by'] = trim($m[1]);
if (preg_match('/\[offset:(.*?)\]/', $content, $m)) $meta['offset'] = (int)$m[1];

$lines = [];
foreach (preg_split('/\r\n|\r|\n/', $content) as $line) {
    // 一行可能包含多个时间标签 [00:12.34][01:20.56]歌词
    if (!preg_match_all('/\[(\d{1,2}):(\d{1,2})(?:\.(\d{1,3}))?\]/', $line, $ms, PREG_SET_ORDER)) continue;
    $text = preg_replace('/\[(\d{1,2}):(\d{1,2})(?:\.(\d{1,3}))?\]/', '', $line);
    $text = trim($text);
    if ($text === '') continue;
    foreach ($ms as $m) {
        $minutes = (int)$m[1];
        $seconds = (int)$m[2];
        $frac = isset($m[3]) && $m[3] !== '' ? (int)$m[3] : 0;
        $frac = $frac / (strlen((string)$m[3]) === 1 ? 10 : (strlen((string)$m[3]) === 2 ? 100 : 1000));
        $time = $minutes * 60 + $seconds + $frac;
        $lines[] = ['t' => round($time, 3), 'text' => $text];
    }
}

// 按时间戳分组：同一时间的多行（如中英对照）合并为一组
$groups = [];
foreach ($lines as $line) {
    $key = $line['t'];
    if (!isset($groups[$key])) $groups[$key] = ['t' => $key, 'texts' => []];
    if (!in_array($line['text'], $groups[$key]['texts'])) $groups[$key]['texts'][] = $line['text'];
}
$lines = array_values($groups);

usort($lines, function ($a, $b) { return $a['t'] <=> $b['t']; });
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'data' => ['meta' => $meta, 'lyrics' => $lines]], JSON_UNESCAPED_UNICODE);
