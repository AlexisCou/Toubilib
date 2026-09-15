<?php

declare(strict_types=1);

use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\ServiceRendezVous;

return [
    PDO::class => function (\Psr\Container\ContainerInterface $c) {
        $db = $c->get('settings')['db'];

        $dsn = sprintf('%s:host=%s;dbname=%s', $db['driver'], $db['host'], $db['database']);

        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    RendezVousRepositoryInterface::class => \DI\autowire(RendezVousRepository::class),
    ServiceRendezVousInterface::class => \DI\autowire(ServiceRendezVous::class),
];
