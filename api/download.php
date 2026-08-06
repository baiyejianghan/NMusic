<?php
// 单曲下载接口：直接输出音频文件，强制下载
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

$mime = [
    'mp3' => 'audio/mpeg', 'flac' => 'audio/flac', 'wav' => 'audio/wav',
    'm4a' => 'audio/mp4', 'ogg' => 'audio/ogg', 'aac' => 'audio/aac',
    'opus' => 'audio/ogg',
];
header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="' . rawurlencode($base) . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
