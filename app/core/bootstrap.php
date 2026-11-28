<?php
define('ROOT', dirname(__DIR__, 2));
$CONFIG = require ROOT . '/config/config.php';
function cfg(string $k) { global $CONFIG; return $CONFIG[$k] ?? null; }

date_default_timezone_set(cfg('timezone') ?: 'UTC');
ini_set('display_errors', cfg('debug') ? '1' : '0');
error_reporting(E_ALL);

$sn = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
if ($sn === '/api' || substr($sn, -4) === '/api') {
    $sn = substr($sn, 0, -4);
}
if ($sn === '.' || $sn === '/') {
    $sn = '';
}
define('BASE', rtrim($sn, '/'));

foreach (['helpers', 'DB', 'Auth', 'Mailer', 'Pdf', 'Xlsx'] as $f) require ROOT . "/app/core/$f.php";
foreach (['Customer', 'Membership', 'Payment', 'Attendance', 'Report'] as $m) require ROOT . "/app/models/$m.php";

set_exception_handler(function (Throwable $e) {
    $logDir = is_writable(ROOT . '/storage') ? (ROOT . '/storage') : sys_get_temp_dir();
    @file_put_contents(rtrim($logDir, '/\\') . '/error.log',
        date('c') . ' ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n", FILE_APPEND);
    http_response_code(500);
    if (($_GET['page'] ?? '') === 'api') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'Server error.' . (cfg('debug') ? ' ' . $e->getMessage() : '')]);
        exit;
    }
    echo '<div style="font-family:sans-serif;padding:40px"><h2>Something went wrong</h2><p>'
        . (cfg('debug') ? htmlspecialchars($e->getMessage()) : 'Please contact the system administrator.') . '</p></div>';
    exit;
});

if (!is_writable(session_save_path() ?: '/nonexistent')) {
    session_save_path(sys_get_temp_dir());
}
session_name('CAATOSESS');
$isHttps = !empty($_SERVER['HTTPS']) || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'httponly' => true,
    'samesite' => 'Lax', 'secure' => $isHttps,
]);
session_start();
