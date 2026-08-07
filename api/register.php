<?php
// 注册接口
require_once __DIR__ . '/common.php';
sessStart();

if (!ALLOW_REGISTER) jsonOut(false, '当前不允许注册');

$username = post('username');
$password = post('password');

if ($username === null || $password === null) jsonOut(false, '缺少参数');
$username = preg_replace('/[\s]/', '', $username);
if (mb_strlen($username) < 2 || mb_strlen($username) > 20) jsonOut(false, '用户名长度需为 2-20 个字符');
if (strlen($password) < 6 || strlen($password) > 72) jsonOut(false, '密码长度需为 6-72 位');

$pdo = db();
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetch()) jsonOut(false, '用户名已存在');

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
try {
    $stmt->execute([$username, $hash]);
} catch (PDOException $e) {
    jsonOut(false, '注册失败，请稍后重试');
}

$_SESSION['uid'] = (int)$pdo->lastInsertId();
$_SESSION['username'] = $username;
jsonOut(true, ['uid' => $_SESSION['uid'], 'username' => $username]);
