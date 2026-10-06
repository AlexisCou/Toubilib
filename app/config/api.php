<?php

declare(strict_types=1);

use toubilib\adapters\http\actions\AnnulerRendezVousAction;
use toubilib\adapters\http\actions\ConsulterPraticienAction;
use toubilib\adapters\http\actions\ConsulterRendezVousAction;
use toubilib\adapters\http\actions\CreerRendezVousAction;
use toubilib\adapters\http\actions\ListerPraticiensAction;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;

return [
    AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    ConsulterRendezVousAction::class => \DI\create(ConsulterRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    CreerRendezVousAction::class => \DI\create(CreerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    ListerPraticiensAction::class => \DI\create(ListerPraticiensAction::class)
        ->constructor(\DI\get(PraticienServiceInterface::class)),

    ConsulterPraticienAction::class => \DI\create(ConsulterPraticienAction::class)
        ->constructor(\DI\get(PraticienServiceInterface::class)),
];
