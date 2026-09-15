<?php

declare(strict_types=1);

use PDO;
use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\ServiceRendezVous;

return [
    PDO::class => function () {
        $driver = $_ENV['lib.driver'] ?? 'pgsql';
        $host = $_ENV['lib.host'] ?? 'toubilib.db';
        $database = $_ENV['lib.database'] ?? 'toubilib';
        $username = $_ENV['lib.username'] ?? '';
        $password = $_ENV['lib.password'] ?? '';

        $dsn = sprintf('%s:host=%s;dbname=%s', $driver, $host, $database);

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    RendezVousRepositoryInterface::class => \DI\autowire(RendezVousRepository::class),
    ServiceRendezVousInterface::class => \DI\autowire(ServiceRendezVous::class),
];
