<?php

declare(strict_types=1);

return [
    'settings' => [
        'db' => [
            'driver' => $_ENV['lib.driver'] ?? 'pgsql',
            'host' => $_ENV['lib.host'] ?? 'toubilib.db',
            'database' => $_ENV['lib.database'] ?? 'toubilib',
            'username' => $_ENV['lib.username'] ?? '',
            'password' => $_ENV['lib.password'] ?? '',
        ],
    ],
];
