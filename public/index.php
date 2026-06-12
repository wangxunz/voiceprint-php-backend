<?php
// public/index.php - 入口路由 (PATH_INFO 模式，不需要 .htaccess)
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/FileUpload.php';
require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../controllers/VoiceprintController.php';
require_once __DIR__ . '/../controllers/ConversionController.php';

$method = $_SERVER['REQUEST_METHOD'];

// PATH_INFO 路由: /VoicePrint/index.php/health -> route = /health
// 也兼容 Rewrite 模式: /VoicePrint/health       -> route = /health
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$reqUri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($pathInfo !== '' && $pathInfo !== '/') {
    // PATH_INFO 模式: /index.php/health
    $route = rtrim($pathInfo, '/');
} else {
    // Rewrite 模式或 query string 降级
    $route = rtrim($reqUri, '/');
    $prefixes = ['/v1', '/VoicePrint'];
    foreach ($prefixes as $prefix) {
        if (strpos($route, $prefix) === 0) {
            $route = substr($route, strlen($prefix)) ?: '/';
            break;
        }
    }
    // 移掉 /index.php 前缀
    if (strpos($route, '/index.php') === 0) {
        $route = substr($route, strlen('/index.php')) ?: '/';
    }
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    switch (true) {
        case $route === '/health' && $method === 'GET':
            (new HealthController())->check();
            break;
        case $route === '/voiceprint/enroll' && $method === 'POST':
            (new VoiceprintController())->enroll();
            break;
        case $route === '/conversion/submit' && $method === 'POST':
            (new ConversionController())->submit();
            break;
        case $route === '/conversion/status' && $method === 'GET':
            (new ConversionController())->status();
            break;
        case $route === '/conversion/result' && $method === 'GET':
            (new ConversionController())->result();
            break;
        case $route === '/conversion/delete' && $method === 'POST':
            (new ConversionController())->delete();
            break;
        case $route === '/conversion/history' && $method === 'GET':
            (new ConversionController())->history();
            break;
        default:
            Response::error('接口不存在: ' . $route, 404);
    }
} catch (\Throwable $e) {
    $msg = (require __DIR__ . '/../config.php')['debug']
        ? $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
        : '服务器内部错误';
    Response::error($msg, 500);
}