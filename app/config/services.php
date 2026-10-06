<?php

declare(strict_types=1);

use toubilib\adapters\persistence\PatientRepository;
use toubilib\adapters\persistence\PraticienRepository;
use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\PraticienService;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\validators\CreateRdvValidator;

return [
    // Nécessaire car Bridge::create() fait passer la résolution des classes internes de Slim
    // (ex. les renderers d'erreur) par le conteneur. Avec useAutowiring(false), \DI\autowire()
    // ne fonctionne pour AUCUNE classe (même déclarée explicitement ici) : il faut \DI\create()
    // avec ->constructor() rempli à la main pour chaque dépendance.
    \Slim\Error\Renderers\PlainTextErrorRenderer::class => \DI\create(),
    \Slim\Error\Renderers\HtmlErrorRenderer::class => \DI\create(),
    \Slim\Error\Renderers\JsonErrorRenderer::class => \DI\create(),
    \Slim\Error\Renderers\XmlErrorRenderer::class => \DI\create(),

    PDO::class => function (\Psr\Container\ContainerInterface $c) {
        $db = $c->get('settings')['db'];

        $dsn = sprintf('%s:host=%s;dbname=%s', $db['driver'], $db['host'], $db['database']);

        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    RendezVousRepositoryInterface::class => \DI\create(RendezVousRepository::class)
        ->constructor(\DI\get(PDO::class)),

    PraticienRepositoryInterface::class => \DI\create(PraticienRepository::class)
        ->constructor(\DI\get(PDO::class)),

    PatientRepositoryInterface::class => \DI\create(PatientRepository::class)
        ->constructor(\DI\get(PDO::class)),

    CreateRdvValidator::class => \DI\create(CreateRdvValidator::class)
        ->constructor(
            \DI\get(PraticienRepositoryInterface::class),
            \DI\get(PatientRepositoryInterface::class),
        ),

    ServiceRendezVousInterface::class => \DI\create(ServiceRendezVous::class)
        ->constructor(
            \DI\get(RendezVousRepositoryInterface::class),
            \DI\get(PraticienRepositoryInterface::class),
            \DI\get(PatientRepositoryInterface::class),
            \DI\get(CreateRdvValidator::class),
        ),

    PraticienServiceInterface::class => \DI\create(PraticienService::class)
        ->constructor(\DI\get(PraticienRepositoryInterface::class)),
];
