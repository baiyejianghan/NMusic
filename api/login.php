<?php
// 登录接口
require_once __DIR__ . '/common.php';
sessStart();

$username = post('username');
$password = post('password');
if ($username === null || $password === null) jsonOut(false, '缺少参数');
$username = preg_replace('/[\s]/', '', $username);

$pdo = db();
$stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonOut(false, '用户名或密码错误');
}

session_regenerate_id(true);
$_SESSION['uid'] = (int)$user['id'];
$_SESSION['username'] = $user['username'];
jsonOut(true, ['uid' => (int)$user['id'], 'username' => $user['username']]);
