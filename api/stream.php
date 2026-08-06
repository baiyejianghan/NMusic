<?php
// 音频流接口：支持 HTTP Range 断点续传（拖动进度条必需）
require_once __DIR__ . '/common.php';

$base = $_GET['file'] ?? '';
$ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
$valid = ['mp3', 'flac', 'wav', 'm4a', 'ogg', 'aac', 'opus'];
if ($base === '' || !safeFileName($base) || !in_array($ext, $valid)) {
    http_response_code(400);
    exit('invalid file');
}

$path = MUSIC_ROOT . '/' . $base;
if (!is_file($path)) {
    http_response_code(404);
    exit('not found');
}

$size = filesize($path);
$mime = [
    'mp3' => 'audio/mpeg', 'flac' => 'audio/flac', 'wav' => 'audio/wav',
    'm4a' => 'audio/mp4', 'ogg' => 'audio/ogg', 'aac' => 'audio/aac',
    'opus' => 'audio/ogg',
];
$ct = $mime[$ext] ?? 'application/octet-stream';

// ---- Range 处理 ----
$start = 0;
$end = $size - 1;
$isRange = false;

if (isset($_SERVER['HTTP_RANGE'])) {
    if (preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
        $isRange = true;
        if ($m[1] !== '') {
            $start = (int)$m[1];
            if ($m[2] !== '') $end = min((int)$m[2], $size - 1);
        } else {
            // 后缀范围 bytes=-500（取最后 500 字节）
            $suffix = (int)$m[2];
            $start = max(0, $size - $suffix);
        }
    }
}

if ($start > $end || $start >= $size) {
    http_response_code(416);
    header('Content-Range: bytes */' . $size);
    exit;
}

if ($isRange) {
    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
    header('Accept-Ranges: bytes');
} else {
    header('Accept-Ranges: bytes');
}

$length = $end - $start + 1;
header('Content-Type: ' . $ct);
header('Content-Length: ' . $length);
header('Cache-Control: no-cache');

$fp = fopen($path, 'rb');
if ($fp === false) {
    http_response_code(500);
    exit('open failed');
}
if ($start > 0) fseek($fp, $start);
$sent = 0;
while ($sent < $length && !feof($fp)) {
    $chunk = fread($fp, min(81920, $length - $sent));
    if ($chunk === false) break;
    echo $chunk;
    $sent += strlen($chunk);
    flush();
}
fclose($fp);
