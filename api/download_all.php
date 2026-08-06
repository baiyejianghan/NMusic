<?php
// 批量下载接口：选择多首歌曲打包为 zip 下载
require_once __DIR__ . '/common.php';

$files = $_GET['files'] ?? '';
if ($files === '') {
    // 未指定则打包整个音乐库
    $files = implode(',', listAudioFiles());
}

$names = [];
foreach (explode(',', $files) as $raw) {
    $name = trim($raw);
    if ($name === '') continue;
    if (!safeFileName($name)) continue;
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp3', 'flac', 'wav', 'm4a', 'ogg', 'aac', 'opus'], true)) continue;
    if (!is_file(MUSIC_ROOT . '/' . $name)) continue;
    $names[] = $name;
}

if (empty($names)) {
    http_response_code(400);
    exit('no files');
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    exit('zip extension missing');
}

// 用缓存目录生成临时 zip（open_basedir 只允许站点目录 + /tmp）
$tmp = sys_get_temp_dir() . '/music_zip_' . bin2hex(random_bytes(6)) . '.zip';
$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::CREATE) !== true) {
    http_response_code(500);
    exit('zip create failed');
}

foreach ($names as $name) {
    $zip->addFile(MUSIC_ROOT . '/' . $name, $name);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="music_library_' . date('Ymd_His') . '.zip"');
header('Content-Length: ' . filesize($tmp));
readfile($tmp);
@unlink($tmp);
