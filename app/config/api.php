<?php

declare(strict_types=1);

use toubilib\adapters\http\actions\AnnulerRendezVousAction;

return [
    AnnulerRendezVousAction::class => \DI\autowire(AnnulerRendezVousAction::class),
];
