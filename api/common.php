<?php
// 公共函数库：数据库连接、响应、文件名安全等

require_once __DIR__ . '/../config.php';

// ---------- 数据库连接（PDO 单例） ----------
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            jsonOut(false, '数据库连接失败');
        }
    }
    return $pdo;
}

// ---------- JSON 输出 ----------
function jsonOut(bool $ok, $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- 输入校验 ----------
function post(string $key): ?string
{
    $v = $_POST[$key] ?? null;
    return $v === null ? null : trim($v);
}

// 校验文件名是否安全（防路径穿越）
function safeFileName(string $name): bool
{
    if ($name === '' || $name !== basename($name)) return false;
    if (preg_match('/\.\.|\\\\|\//', $name)) return false;
    return true;
}

// 去掉扩展名，返回 basename（如 海阔天空.mp3 -> 海阔天空）
function stripExt(string $name): string
{
    return preg_replace('/\.[^.]+$/', '', $name);
}

// ---------- Session（与面板隔离） ----------
function sessStart(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}

// ---------- 扫描音乐文件 ----------
function listAudioFiles(): array
{
    $exts = ['mp3', 'flac', 'wav', 'm4a', 'ogg', 'aac', 'opus'];
    $files = [];
    $musicDir = MUSIC_ROOT;
    if (!is_dir($musicDir)) return $files;
    $dh = opendir($musicDir);
    if ($dh === false) return $files;
    while (($f = readdir($dh)) !== false) {
        if ($f === '.' || $f === '..') continue;
        $p = $musicDir . '/' . $f;
        if (!is_file($p)) continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, $exts)) {
            $files[] = $f;
        }
    }
    closedir($dh);
    sort($files, SORT_STRING);
    return $files;
}

// 获取音频时长（秒，浮点），用 ffprobe 并缓存到站点缓存目录
function getAudioDuration(string $file): ?float
{
    $path = MUSIC_ROOT . '/' . $file;
    if (!is_file($path)) return null;

    $cacheDir = MUSIC_ROOT . '/.cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    $cacheFile = $cacheDir . '/durations.json';
    $cache = [];
    if (is_file($cacheFile)) {
        $j = @json_decode(@file_get_contents($cacheFile), true);
        if (is_array($j)) $cache = $j;
    }
    $key = md5($file);
    $mtime = @filemtime($path);

    if (isset($cache[$key]) && isset($cache[$key]['m']) && $cache[$key]['m'] === $mtime) {
        return $cache[$key]['d'];
    }

    $dur = null;
    $disabled = array_flip(array_map('trim', explode(',', (string)ini_get('disable_functions'))));
    if (function_exists('exec') && !isset($disabled['exec'])) {
        $cmd = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg($path) . ' 2>/dev/null';
        @exec($cmd, $out, $ret);
        if ($ret === 0 && isset($out[0]) && is_numeric(trim($out[0]))) {
            $dur = (float)trim($out[0]);
        }
    }

    if ($dur !== null && $dur >= 0) {
        $cache[$key] = ['m' => $mtime, 'd' => $dur];
        @file_put_contents($cacheFile, json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    return $dur;
}

// 将文件名按「-」拆成片段（去掉扩展名、去空段）
function nameParts(string $name): array
{
    $base = stripExt($name);
    $parts = preg_split('/\s*-\s*/', $base);
    $out = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '') $out[] = $p;
    }
    return $out;
}

// 判断歌词/封面文件名是否匹配某首歌。
// 规则：优先完全同名；否则把双方按「-」拆解，
// 只要任一非空片段相同即视为匹配。
// 支持：歌名 ↔ 歌名-歌手 / 歌手-歌名 / 歌手 - 歌名
function nameMatchesFile(string $name, string $base): bool
{
    if (stripExt($name) === $base) return true;
    $np = nameParts($name);
    $bp = nameParts($base);
    foreach ($bp as $b) {
        foreach ($np as $n) {
            if ($b === $n) return true;
        }
    }
    return false;
}

// 查找某首歌对应的封面文件，返回【文件名】或 null
function findCoverFile(string $base): ?string
{
    $dir = MUSIC_ROOT . '/' . COVER_DIR_NAME;
    if (!is_dir($dir)) return null;
    $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
    $dh = opendir($dir);
    if ($dh === false) return null;
    $matched = null;
    while (($f = readdir($dh)) !== false) {
        if ($f === '.' || $f === '..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $exts)) continue;
        if (nameMatchesFile($f, $base)) { $matched = $f; break; }
    }
    closedir($dh);
    return $matched;
}

// 查找某首歌对应的歌词文件，返回【文件名】或 null
function findLyricFile(string $base): ?string
{
    $dir = MUSIC_ROOT . '/' . LYRIC_DIR_NAME;
    if (!is_dir($dir)) return null;
    $dh = opendir($dir);
    if ($dh === false) return null;
    $matched = null;
    while (($f = readdir($dh)) !== false) {
        if ($f === '.' || $f === '..') continue;
        if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) !== 'lrc') continue;
        if (nameMatchesFile($f, $base)) { $matched = $f; break; }
    }
    closedir($dh);
    return $matched;
}

// 极简解析 ID3v2 标题/歌手/专辑（mp3 嵌入信息），失败返回 null
function readMp3Meta(string $file): ?array
{
    $fp = @fopen($file, 'rb');
    if (!$fp) return null;
    $h = fread($fp, 10);
    if (strlen($h) < 10 || substr($h, 0, 3) !== 'ID3') { fclose($fp); return null; }
    $size = 0;
    for ($i = 6; $i < 10; $i++) $size = ($size << 7) | (ord($h[$i]) & 0x7F);
    if ($size <= 0 || $size > 10 * 1024 * 1024) { fclose($fp); return null; }
    $body = stream_get_contents($fp, $size);
    fclose($fp);
    if ($body === false || strlen($body) < $size) $body = (string)$body;

    $meta = ['title' => null, 'artist' => null, 'album' => null];
    $ver = ord($h[3]);
    $off = 0;
    $len = strlen($body);
    while ($off + 10 <= $len) {
        $id = substr($body, $off, 4);
        if (!preg_match('/^[A-Z0-9]{4}$/', $id)) break;
        $frameLen = 0;
        if ($ver === 3) {
            $b = substr($body, $off + 4, 4);
            $frameLen = unpack('N', $b)[1];
            $flagLen = 0;
        } else {
            $b = substr($body, $off + 4, 4);
            for ($i = 0; $i < 4; $i++) $frameLen = ($frameLen << 7) | (ord($b[$i]) & 0x7F);
            $flags = unpack('n', substr($body, $off + 8, 2))[1];
            $flagLen = ($flags & 0x0002) ? 4 : 0; // ID3v2.4 有 Data Length Indicator
        }
        $frameLen += $flagLen;
        if ($frameLen <= 0 || $off + 10 + $frameLen > $len) break;
        $frame = substr($body, $off + 10, $frameLen - $flagLen);
        if (in_array($id, ['TIT2', 'TPE1', 'TALB'], true)) {
            $enc = ord($frame[0]);
            $txt = substr($frame, 1);
            // ID3v2 文本编码：0=ISO-8859-1  1=UTF-16(带BOM)  2=UTF-16BE  3=UTF-8
            if (($enc === 1 || $enc === 2) && function_exists('mb_convert_encoding')) {
                $from = ($enc === 2) ? 'UTF-16BE' : 'UTF-16';
                $c = @mb_convert_encoding($txt, 'UTF-8', $from);
                if ($c !== false) $txt = $c;
            } elseif ($enc === 0 && function_exists('mb_convert_encoding')) {
                $c = @mb_convert_encoding($txt, 'UTF-8', 'ISO-8859-1');
                if ($c !== false) $txt = $c;
            }
            // enc===3 即为 UTF-8，无需转换
            $txt = str_replace("\x00", '', $txt);
            $txt = trim($txt);
            if ($txt !== '') {
                if ($id === 'TIT2') $meta['title'] = $txt;
                if ($id === 'TPE1') $meta['artist'] = $txt;
                if ($id === 'TALB') $meta['album'] = $txt;
            }
        }
        $off += 10 + $frameLen;
    }
    if ($meta['title'] === null && $meta['artist'] === null) return null;
    return $meta;
}
