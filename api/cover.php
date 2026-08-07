<?php
// 封面接口：返回图片，支持缓存
require_once __DIR__ . '/common.php';

$base = $_GET['file'] ?? '';
if ($base === '' || !safeFileName($base)) {
    http_response_code(404);
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="100%" height="100%" fill="#2a2a2e"/><g fill="none" stroke="#888" stroke-width="12" stroke-linecap="round" stroke-linejoin="round" transform="translate(50,70) scale(8.3)"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></g></svg>';
    exit;
}

$coverFile = findCoverFile($base);
if ($coverFile === null) {
    http_response_code(404);
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="100%" height="100%" fill="#2a2a2e"/><g fill="none" stroke="#888" stroke-width="12" stroke-linecap="round" stroke-linejoin="round" transform="translate(50,70) scale(8.3)"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></g></svg>';
    exit;
}

$path = MUSIC_ROOT . '/' . COVER_DIR_NAME . '/' . $coverFile;
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = [
    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
    'webp' => 'image/webp', 'gif' => 'image/gif', 'bmp' => 'image/bmp',
];
$ct = $mime[$ext] ?? 'application/octet-stream';

// 简单缓存
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $mtime = filemtime($path);
    if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mtime) {
        http_response_code(304);
        exit;
    }
}

header('Content-Type: ' . $ct);
header('Cache-Control: max-age=86400');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
header('Content-Length: ' . filesize($path));
readfile($path);
