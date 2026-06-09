<?php
// public/index.php - ??????????????
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/FileUpload.php';
require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../controllers/VoiceprintController.php';
require_once __DIR__ . '/../controllers/ConversionController.php';

\ = \['REQUEST_METHOD'];
\    = parse_url(\['REQUEST_URI'], PHP_URL_PATH);
\    = rtrim(\, '/');

// ????????????????/v1?/VoicePrint ??
\ = \;
\ = ['/v1', '/VoicePrint'];
foreach (\ as \) {
    if (strpos(\, \) === 0) {
        \ = substr(\, strlen(\)) ?: '/';
        break;
    }
}

// CORS ?
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if (\ === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ????
try {
    switch (true) {
        case \ === '/health' && \ === 'GET':
            (new HealthController())->check();
            break;

        case \ === '/voiceprint/enroll' && \ === 'POST':
            (new VoiceprintController())->enroll();
            break;

        case \ === '/conversion/submit' && \ === 'POST':
            (new ConversionController())->submit();
            break;

        case \ === '/conversion/status' && \ === 'GET':
            (new ConversionController())->status();
            break;

        case \ === '/conversion/result' && \ === 'GET':
            (new ConversionController())->result();
            break;

        case \ === '/conversion/delete' && \ === 'POST':
            (new ConversionController())->delete();
            break;

        case \ === '/conversion/history' && \ === 'GET':
            (new ConversionController())->history();
            break;

        default:
            Response::error('?????: ' . \, 404);
    }
} catch (\Throwable \) {
    \ = (require __DIR__ . '/../config.php')['debug']
        ? \->getMessage() . ' in ' . \->getFile() . ':' . \->getLine()
        : '???????';
    Response::error(\, 500);
}
