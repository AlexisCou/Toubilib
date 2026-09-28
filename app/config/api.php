<?php

declare(strict_types=1);

use toubilib\adapters\http\actions\AnnulerRendezVousAction;
use toubilib\adapters\http\actions\ConsulterPraticienAction;
use toubilib\adapters\http\actions\ConsulterRendezVousAction;
use toubilib\adapters\http\actions\ListerPraticiensAction;

return [
    AnnulerRendezVousAction::class => \DI\autowire(AnnulerRendezVousAction::class),
    ConsulterRendezVousAction::class => \DI\autowire(ConsulterRendezVousAction::class),
    ListerPraticiensAction::class => \DI\autowire(ListerPraticiensAction::class),
    ConsulterPraticienAction::class => \DI\autowire(ConsulterPraticienAction::class),
];
