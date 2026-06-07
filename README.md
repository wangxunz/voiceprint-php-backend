# 🎤 声纹变声 - PHP 后端服务

用个人声纹替换歌曲原声的微信小程序后端 API 服务。

## 🏗 架构

```
微信小程序
    │
    ▼
┌──────────────────────┐
│  Nginx (public/)      │  ← 反向代理 + 静态资源
│  ├─ index.php (路由)   │
│  ├─ /results/*.mp3    │  ← 结果文件直接服务
│  └─ /v1/* → PHP       │
└──────┬───────────────┘
       │
┌──────▼───────────────┐
│  PHP API 层           │
│  ├─ HealthController  │  GET /v1/health
│  ├─ VoiceprintController │ POST /v1/voiceprint/enroll
│  └─ ConversionController │ POST /v1/conversion/submit
│                          │ GET  /v1/conversion/status
│                          │ GET  /v1/conversion/result
│                          │ POST /v1/conversion/delete
│                          │ GET  /v1/conversion/history
└──────┬───────────────┘
       │
┌──────▼───────────────┐
│  MySQL 数据库          │
│  ├─ voiceprints       │  声纹信息
│  └─ conversion_tasks  │  变声任务
└──────┬───────────────┘
       │
┌──────▼───────────────┐
│  Python Worker        │  异步处理
│  ├─ voiceprint_enroll │  声纹特征提取 (Resemblyzer)
│  └─ voice_convert     │  变声流水线 (Spleeter/Demucs + RVC/OpenVoice)
└──────────────────────┘
```

## 📁 目录结构

```
voiceprint-php-backend/
├── public/                   # Web 根目录
│   ├── index.php             # 入口 + 路由
│   └── .htaccess             # Apache 重写规则
├── controllers/
│   ├── HealthController.php
│   ├── VoiceprintController.php
│   └── ConversionController.php
├── utils/
│   ├── Database.php          # PDO 数据库封装
│   ├── Response.php          # JSON 响应
│   └── FileUpload.php        # 文件上传验证
├── workers/
│   ├── voiceprint_enroll.py  # 声纹提取脚本
│   ├── voice_convert.py      # 变声处理脚本
│   └── requirements.txt      # Python 依赖
├── sql/
│   └── schema.sql            # 数据库建表
├── uploads/                  # 上传文件存储
│   ├── voiceprints/
│   ├── songs/
│   └── temp/
├── results/                  # 变声结果
├── logs/                     # 日志
├── config.php                # 配置文件
└── deploy/                   # 部署脚本（可选）
```

## 🚀 快速部署

### 1. 环境要求

| 组件 | 版本 |
|------|------|
| PHP | >= 8.0 |
| MySQL | >= 5.7 (或 MariaDB 10.2+) |
| Python | >= 3.9 |
| Nginx | >= 1.18 (或 Apache 2.4) |
| FFmpeg | >= 4.0 |

### 2. 安装 PHP 依赖

```bash
# 安装必要的 PHP 扩展
sudo apt install php8.2 php8.2-mysql php8.2-mbstring php8.2-fileinfo
```

### 3. 初始化数据库

```bash
mysql -u root -p < sql/schema.sql
```

### 4. 安装 Python 依赖

```bash
cd workers
pip install -r requirements.txt

# （可选）安装 AI 模型依赖
pip install spleeter          # 人声分离
# 或
pip install demucs            # 更优的人声分离

# 声纹转换（选一）
# RVC: git clone https://github.com/RVC-Project/Retrieval-based-Voice-Conversion-WebUI.git
# OpenVoice: git clone https://github.com/myshell-ai/OpenVoice.git
```

### 5. 配置环境变量

```bash
cp .env.example .env
# 编辑 .env 设置数据库密码、域名等
```

或直接编辑 `config.php`：

```php
return [
    'db' => [
        'host'     => '127.0.0.1',
        'database' => 'voiceprint_converter',
        'username' => 'root',
        'password' => 'your_password_here',
    ],
    'result_base_url' => 'https://your-domain.com/results',
];
```

### 6. 配置 Nginx

```nginx
server {
    listen 80;
    server_name api.example.com;
    root /var/www/voiceprint-php-backend/public;
    index index.php;

    # 结果文件直链
    location /results/ {
        alias /var/www/voiceprint-php-backend/results/;
        add_header Content-Disposition 'attachment';
        add_header Cache-Control 'public, max-age=3600';
    }

    # API 路由
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 上传大小限制
    client_max_body_size 32M;
}
```

### 7. 设置目录权限

```bash
sudo chown -R www-data:www-data uploads/ results/ logs/
sudo chmod -R 755 uploads/ results/ logs/
```

### 8. 启动

```bash
sudo systemctl restart nginx php8.2-fpm
```

## 📡 API 文档

### 健康检查
```
GET /v1/health
→ {"code":0,"data":{"status":"online","checks":{"database":"ok",...}}}
```

### 上传声纹
```
POST /v1/voiceprint/enroll
Content-Type: multipart/form-data
  voice_sample: <音频文件>
  duration: 30
→ {"code":0,"data":{"voiceprintId":"vp_xxx","status":"pending"}}
```

### 提交变声
```
POST /v1/conversion/submit
Content-Type: multipart/form-data
  song_file: <歌曲文件>
  voiceprintId: "vp_xxx"
  songName: "七里香"
  pitchShift: "2"
→ {"code":0,"data":{"taskId":"task_xxx","state":"pending"}}
```

### 查询状态
```
GET /v1/conversion/status?taskId=task_xxx
→ {"code":0,"data":{"state":"converting","progress":60}}
```

### 获取结果
```
GET /v1/conversion/result?taskId=task_xxx
→ {"code":0,"data":{"resultUrl":"https://.../task_xxx.mp3"}}
```

状态流转: `pending → separating → converting → rendering → completed / failed`

## 🔧 生产环境建议

1. **队列系统**: 用 Redis + Laravel Queue 替代直接 exec() 异步调用
2. **GPU 加速**: 声纹转换部署在 GPU 服务器，大幅提升速度
3. **对象存储**: 结果文件存到 OSS/S3，减轻服务器压力
4. **CDN 加速**: 结果文件通过 CDN 分发
5. **WebSocket**: 实时推送处理进度给小程序

## 📄 License

MIT
