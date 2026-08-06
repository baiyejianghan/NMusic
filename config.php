<?php
// 个人音乐网站 - 全局配置
// 请根据实际部署修改数据库信息

// ===== 数据库配置（MySQL）=====
define('DB_HOST', '127.0.0.1');            // MySQL 地址
define('DB_PORT', 3306);
define('DB_USER', 'your_db_user');         // 数据库用户名
define('DB_PASS', 'your_db_password');     // 数据库密码
define('DB_NAME', 'your_db_name');         // 数据库名

// ===== 音乐文件夹配置 =====
// 站点根目录下的 music 文件夹（音频平铺 + 歌词/ + 封面/ 两个子文件夹）
define('MUSIC_ROOT', __DIR__ . '/music');
define('LYRIC_DIR_NAME', '歌词');          // 歌词子文件夹名
define('COVER_DIR_NAME', '封面');          // 封面子文件夹名

// ===== 功能开关 =====
define('ALLOW_REGISTER', false);           // 是否允许新用户注册
define('SESSION_NAME', 'MUSICWEB_SID');    // Session 名，防止与面板冲突

// ===== 安全 =====
// 文件名校验，只允许正常音频/歌词/封面文件名
define('SAFE_FILE_PATTERN', '/^[\w\-\s\.\u4e00-\u9fa5()（）\[\]【】,+，。#&、（）]{1,200}$/u');

// ===== 顶部跳转链接（改成你自己的） =====
define('LINK_GITHUB', 'https://github.com/你的用户名/你的仓库');  // GitHub 仓库地址
define('LINK_SPONSOR', 'https://你的赞助页面地址');              // 赞助 / 打赏页面地址
