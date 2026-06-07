<?php
// config.php - 应用配置
return [
    // 数据库
    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_NAME') ?: 'voiceprint_converter',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8mb4',
    ],

    // 文件存储路径（相对于项目根目录）
    'paths' => [
        'uploads'      => __DIR__ . '/uploads',
        'voiceprints'  => __DIR__ . '/uploads/voiceprints',
        'songs'        => __DIR__ . '/uploads/songs',
        'temp'         => __DIR__ . '/uploads/temp',
        'results'      => __DIR__ . '/results',
        'logs'         => __DIR__ . '/logs',
    ],

    // 上传限制
    'upload' => [
        'voiceprint_max_size' => 10 * 1024 * 1024,   // 10MB
        'song_max_size'       => 30 * 1024 * 1024,   // 30MB
        'voiceprint_min_duration' => 5,               // 最少 5 秒
        'voiceprint_max_duration' => 120,             // 最多 120 秒
        'allowed_audio_types' => ['wav', 'mp3', 'm4a', 'aac', 'flac', 'ogg'],
    ],

    // Python 环境
    'python' => [
        'path'          => getenv('PYTHON_PATH') ?: 'python3',
        'enroll_script' => __DIR__ . '/workers/voiceprint_enroll.py',
        'convert_script'=> __DIR__ . '/workers/voice_convert.py',
        'spleeter_path' => getenv('SPLEETER_PATH') ?: 'spleeter',
        'rvc_path'      => getenv('RVC_PATH') ?: '',
        'timeout'       => 600,  // 处理超时（秒）
    ],

    // 结果文件 URL 基础路径
    'result_base_url' => getenv('RESULT_BASE_URL') ?: 'https://api.example.com/results',

    // 历史记录每页条数
    'history_page_size' => 20,

    // 调试模式
    'debug' => getenv('APP_DEBUG') === 'true',
];