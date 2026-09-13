<?php

declare(strict_types=1);

return [
    'name' => 'Sheepy',
    'timezone' => 'Asia/Singapore',
    'error_log' => dirname(__DIR__) . '/storage/logs/error.log',
    'session' => [
        'name' => 'sheepy_session',
        'lifetime' => 0,
        'idle_timeout' => 1800,
        'rotation_interval' => 900,
        'path' => '/',
        'samesite' => 'Lax',
    ],
];
