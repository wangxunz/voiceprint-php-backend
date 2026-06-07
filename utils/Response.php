<?php
// utils/Response.php - JSON 响应工具
class Response
{
    public static function success($data = null, string $message = 'ok', int $httpCode = 200): void
    {
        http_response_code($httpCode);
        echo json_encode([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $message = 'error', int $httpCode = 400, $data = null): void
    {
        http_response_code($httpCode);
        echo json_encode([
            'code'    => $httpCode,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function paginate(array $list, int $total, int $page, int $pageSize): void
    {
        self::success([
            'list'     => $list,
            'total'    => $total,
            'page'     => $page,
            'pageSize' => $pageSize,
            'hasMore'  => ($page * $pageSize) < $total,
        ]);
    }
}