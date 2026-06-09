<?php
// public/index.php - 入口路由 (多前缀适配)
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/FileUpload.php';
require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../controllers/VoiceprintController.php';
require_once __DIR__ . '/../controllers/ConversionController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');

// +++ 改动在这里：原来只认 /v1，现在循环匹配多个前缀
$route = $uri;
$prefixes = ['/v1', '/VoicePrint'];
foreach ($prefixes as $prefix) {
    if (strpos($uri, $prefix) === 0) {
        $route = substr($uri, strlen($prefix)) ?: '/';
        break;
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