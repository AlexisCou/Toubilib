<?php

declare(strict_types=1);

use Slim\App;
use toubilib\adapters\http\actions\AnnulerRendezVousAction;

return function (App $app): void {
    $app->patch('/rdv/{id}/annulation', AnnulerRendezVousAction::class);
};
