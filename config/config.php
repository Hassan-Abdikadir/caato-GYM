<?php
// Database & application configuration (Local MySQL defaults; supports Vercel / Cloud Env Variables & Auto-SQLite fallback)
return [
    'db' => [
        'driver'  => getenv('DB_DRIVER') ?: 'mysql',
        'host'    => getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1'),
        'port'    => getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306'),
        'name'    => getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'caato_gym'),
        'user'    => getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root'),
        'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') ?: ''),
        'charset' => 'utf8mb4',
    ],
    'timezone' => 'Africa/Mogadishu',
    'session_minutes' => 60,   // idle timeout
    'debug' => (bool) (getenv('APP_DEBUG') ?: false), // set true only while troubleshooting
];
