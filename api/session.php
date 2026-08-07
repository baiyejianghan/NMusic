<?php
// 会话查询 / 退出登录
require_once __DIR__ . '/common.php';
sessStart();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // 退出
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    jsonOut(true, '已退出');
}

$uid = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;
if ($uid > 0) {
    jsonOut(true, ['logged_in' => true, 'uid' => $uid, 'username' => $_SESSION['username'] ?? '']);
} else {
    jsonOut(true, ['logged_in' => false]);
}
