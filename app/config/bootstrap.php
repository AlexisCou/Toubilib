<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->addDefinitions(__DIR__ . '/settings.php');
$containerBuilder->addDefinitions(__DIR__ . '/services.php');
$containerBuilder->addDefinitions(__DIR__ . '/api.php');
$container = $containerBuilder->build();

$app = Bridge::create($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(displayErrorDetails: true, logErrors: true, logErrorDetails: true);

// Middleware CORS, ajouté en dernier pour qu'il englobe tout le reste (y compris les réponses
// d'erreur) : les en-têtes Access-Control-* doivent être présents sur toutes les réponses, pas
// seulement sur les succès.
$app->add(function (Request $request, Handler $handler): Response {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
});

(require __DIR__ . '/routes.php')($app);

return $app;
