<?php
// public/index.php - 入口路由
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/FileUpload.php';
require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../controllers/VoiceprintController.php';
require_once __DIR__ . '/../controllers/ConversionController.php';

// 解析请求
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');

// 移除 /v1 前缀
$basePath = '/v1';
if (strpos($uri, $basePath) === 0) {
    $route = substr($uri, strlen($basePath)) ?: '/';
} else {
    $route = $uri;
}

// CORS 头
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 路由分发
try {
    switch (true) {
        // 健康检查
        case $route === '/health' && $method === 'GET':
            (new HealthController())->check();
            break;

        // 声纹注册
        case $route === '/voiceprint/enroll' && $method === 'POST':
            (new VoiceprintController())->enroll();
            break;

        // 提交变声任务
        case $route === '/conversion/submit' && $method === 'POST':
            (new ConversionController())->submit();
            break;

        // 查询任务状态
        case $route === '/conversion/status' && $method === 'GET':
            (new ConversionController())->status();
            break;

        // 获取结果
        case $route === '/conversion/result' && $method === 'GET':
            (new ConversionController())->result();
            break;

        // 删除任务
        case $route === '/conversion/delete' && $method === 'POST':
            (new ConversionController())->delete();
            break;

        // 历史记录
        case $route === '/conversion/history' && $method === 'GET':
            (new ConversionController())->history();
            break;

        // 404
        default:
            Response::error('接口不存在', 404);
    }
} catch (\Throwable $e) {
    $msg = (require __DIR__ . '/../config.php')['debug']
        ? $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
        : '服务器内部错误';
    Response::error($msg, 500);
}