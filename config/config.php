<?php
// Database & application configuration (Local MySQL defaults; remote InfinityFree fallback in comment)
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'caato_gym',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'timezone' => 'Africa/Mogadishu',
    'session_minutes' => 60,   // idle timeout
    'debug' => false,          // set true only while troubleshooting
];
