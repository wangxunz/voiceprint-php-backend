<?php
class HealthController
{
    public function check(): void
    {
        $config = require __DIR__ . '/../config.php';

        // 检查数据库连接
        $dbOk = false;
        try {
            Database::query('SELECT 1');
            $dbOk = true;
        } catch (\Throwable $e) {
            // 数据库不可用
        }

        // 检查目录权限
        $paths = $config['paths'];
        $uploadsWritable = is_writable($paths['uploads']);
        $resultsWritable = is_writable($paths['results']);

        // Python 环境检查
        $pythonPath = $config['python']['path'];
        $pythonOk = false;
        $pythonVersion = '';
        $pythonCmd = escapeshellcmd($pythonPath);
        $output = [];
        $retCode = 0;
        exec(sprintf('%s --version 2>&1', $pythonCmd), $output, $retCode);
        if ($retCode === 0 && !empty($output)) {
            $pythonOk = true;
            $pythonVersion = $output[0] ?? '';
        }

        Response::success([
            'status'   => $dbOk ? 'online' : 'degraded',
            'checks'   => [
                'database'    => $dbOk ? 'ok' : 'error',
                'uploads'     => $uploadsWritable ? 'ok' : 'error',
                'results'     => $resultsWritable ? 'ok' : 'error',
                'python'      => $pythonOk ? 'ok' : 'error',
                'python_ver'  => $pythonVersion,
            ],
            'time'     => date('c'),
        ]);
    }
}
