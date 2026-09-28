<?php

declare(strict_types=1);

use Slim\App;
use toubilib\adapters\http\actions\AnnulerRendezVousAction;
use toubilib\adapters\http\actions\ConsulterPraticienAction;
use toubilib\adapters\http\actions\ConsulterRendezVousAction;
use toubilib\adapters\http\actions\ListerPraticiensAction;

return function (App $app): void {
    $app->patch('/rdv/{id}/annulation', AnnulerRendezVousAction::class);
    $app->get('/rdv/{id}', ConsulterRendezVousAction::class);

    $app->get('/praticiens', ListerPraticiensAction::class);
    $app->get('/praticiens/{id}', ConsulterPraticienAction::class);
};
