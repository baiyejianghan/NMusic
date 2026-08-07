# NMusic

一个 Apple Music 风格的个人音乐播放网站（PHP + nginx + 原生 JS），支持本机音频文件、歌词与封面展示、沉浸式播放页。

**当前稳定版本：V58**

## 功能特性

- 🎵 **本地音乐库**：扫描 `music/` 文件夹下的音频（m4a/mp3/flac 等），自动读取元数据
- 📝 **歌词**：`music/歌词/` 文件夹内 LRC 歌词，沉浸播放页实时滚动高亮，支持逐字同步（多语言）
- 🖼️ **封面**：`music/封面/` 文件夹内图片自动匹配歌曲
- ▶️ **沉浸式播放页**：Apple Music 风格，封面 + 歌词切换、背景色随封面取色
- 🔍 **模糊搜索**：按空格分词、跨标题/艺术家/专辑字段、支持字符顺序模糊匹配
- 📱 **移动端适配**：响应式布局、安全区适配、触屏优化
- 🚀 **加载提速**：catalog.json 元数据缓存 + ETag/304 + gzip 压缩；音频流走 nginx X-Accel 零拷贝
- 💾 **本地缓存**：歌单 / 最近播放 / 喜欢 / 音量 / 循环模式存于 localStorage，下次秒开
- 🔑 **用户系统**：注册 / 登录 / 退出（可选）

## 目录结构

```
├── index.php              # 入口（SPA）
├── config.php             # 全局配置（数据库/路径/链接）
├── music/                 # 音乐文件夹（需自行创建）
│   ├── *.m4a|*.mp3|...    # 音频文件平铺
│   ├── 歌词/               # LRC 歌词
│   └── 封面/               # 封面图片
├── api/                   # 后端接口
│   ├── songs.php          # 歌曲列表（扫描 + catalog 缓存 + ETag/gzip）
│   ├── stream.php         # 音频流（X-Accel 内部重定向，支持 Range/206）
│   ├── lyrics.php         # 歌词（LRC 解析）
│   ├── cover.php          # 封面（含默认占位）
│   ├── download.php       # 单曲下载
│   ├── download_all.php   # 批量下载（ZipArchive）
│   ├── login.php          # 登录
│   ├── register.php       # 注册
│   └── session.php        # 会话查询 / 退出
└── assets/
    ├── logo.png
    ├── css/style.css      # Apple Music 风格样式
    └── js/ (app|api|player|views).js
```

## 部署

1. 准备环境：PHP 7.3+（需要 `pdo_mysql` 扩展）、nginx、MySQL
2. 将代码放到站点根目录
3. 修改 `config.php` 中的数据库信息与链接
4. 创建 `music/` 文件夹并放入音频（可选 `歌词/`、`封面/` 子文件夹）
5. 访问站点即可

> 媒体分发（可选）：在 nginx 站点配置中加入内部 location，配合 `stream.php` 的 `X-Accel-Redirect` 实现零拷贝音频流：

```nginx
location /protected-music/ {
    alias /path/to/music/;
    internal;
    expires 7d;
}
```

## 数据库

性能 `users` 表存用户登录信息（`username` / `password_hash`），使用 `password_hash()` 加密。歌曲元数据缓存在 `music/.cache/`（不入库）。

```sql
CREATE TABLE users (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(50)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## License

[MIT](LICENSE)