<?php
// 音频流接口：PHP 校验后交给 nginx X-Accel 内部重定向发送（零拷贝、原生 Range 支持）
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
$mtime = filemtime($path);
$etag = '"' . md5($size . '-' . $mtime) . '"';
$mime = [
    'mp3' => 'audio/mpeg', 'flac' => 'audio/flac', 'wav' => 'audio/wav',
    'm4a' => 'audio/mp4', 'ogg' => 'audio/ogg', 'aac' => 'audio/aac',
    'opus' => 'audio/ogg',
];
$ct = $mime[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $ct);
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=86400, stale-while-revalidate=604800');

// 304 缓存校验（文件名编码需 URI 规范）
header('ETag: ' . $etag);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
    $inm = trim($_SERVER['HTTP_IF_NONE_MATCH']);
    if (strncmp($inm, 'W/', 2) === 0) $inm = trim(substr($inm, 2));
    if ($inm === $etag) { http_response_code(304); exit; }
}
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $ims = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($ims !== false && $ims >= $mtime) { http_response_code(304); exit; }
}

// 交给 nginx 内部发送文件（nginx 原生处理 Range/206）
// 注意：base 已通过 safeFileName（无 ..、无斜杠），但文件名含空格/中文，需 URL 编码为 URI
header('X-Accel-Redirect: ' . '/protected-music/' . rawurlencode($base));