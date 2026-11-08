<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use toubilib\adapters\http\actions\AnnulerRendezVousAction;
use toubilib\adapters\http\actions\ConsulterPraticienAction;
use toubilib\adapters\http\actions\ConsulterRendezVousAction;
use toubilib\adapters\http\actions\CreerRendezVousAction;
use toubilib\adapters\http\actions\HonererRendezVousAction;
use toubilib\adapters\http\actions\IgnererRendezVousAction;
use toubilib\adapters\http\actions\ListerCreneauxPraticienAction;
use toubilib\adapters\http\actions\ListerPraticiensAction;
use toubilib\adapters\http\actions\ObtenirAgendaPraticienAction;

return function (App $app): void {
    $app->post('/rdvs', CreerRendezVousAction::class);
    $app->get('/rdvs/{id}', ConsulterRendezVousAction::class);
    $app->patch('/rdvs/{id}/annuler', AnnulerRendezVousAction::class);
    $app->patch('/rdvs/{id}/honorer', HonererRendezVousAction::class);
    $app->patch('/rdvs/{id}/ignorer', IgnererRendezVousAction::class);

    $app->get('/praticiens', ListerPraticiensAction::class);
    $app->get('/praticiens/{id}', ConsulterPraticienAction::class);
    $app->get('/praticiens/{id}/rdvs', ListerCreneauxPraticienAction::class);
    $app->get('/praticiens/{id}/agenda', ObtenirAgendaPraticienAction::class);

    // Préflight CORS : le navigateur envoie OPTIONS avant toute requête "non simple"
    // (ex. Content-Type: application/json, ou méthode PATCH/DELETE). On répond 204 sans corps,
    // les en-têtes Access-Control-* étant déjà ajoutés par le middleware CORS global.
    $app->options('/{routes:.+}', function (Request $request, Response $response): Response {
        return $response->withStatus(204);
    });
};
