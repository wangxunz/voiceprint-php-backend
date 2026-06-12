<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
echo "<pre>=== PHP ===\n";
echo "Version: " . phpversion() . "\n";
echo "exec(): " . (function_exists('exec') ? 'YES' : 'NO') . "\n";
echo "PDO: " . (class_exists('PDO') ? 'YES' : 'NO') . "\n";
echo "\n=== Files ===\n";
foreach (['utils', 'controllers'] as $d) {
    $p = __DIR__ . '/../' . $d;
    if (is_dir($p)) foreach (scandir($p) as $f) if ($f!=='.'&&$f!=='..') echo "  $d/$f: " . (file_exists("$p/$f")?'OK':'MISSING') . "\n";
}
echo "\n=== Config ===\n";
try { require __DIR__ . '/../config.php'; echo "OK\n"; } catch (Throwable $e) { echo "ERROR: " . $e->getMessage() . "\n"; }
echo "\n=== Includes ===\n";
foreach (['utils/Response.php','utils/Database.php','utils/FileUpload.php','controllers/HealthController.php'] as $f) {
    try { require_once __DIR__ . '/../' . $f; echo "  $f: loaded\n"; } catch (Throwable $e) { echo "  $f: ERROR - " . $e->getMessage() . "\n"; }
}
echo "\n=== END ===";
