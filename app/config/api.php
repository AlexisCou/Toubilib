<?php

declare(strict_types=1);

use toubilib\adapters\http\actions\AnnulerRendezVousAction;
use toubilib\adapters\http\actions\ConsulterPraticienAction;
use toubilib\adapters\http\actions\ConsulterRendezVousAction;
use toubilib\adapters\http\actions\CreerRendezVousAction;
use toubilib\adapters\http\actions\HonererRendezVousAction;
use toubilib\adapters\http\actions\IgnererRendezVousAction;
use toubilib\adapters\http\actions\ListerCreneauxPraticienAction;
use toubilib\adapters\http\actions\ListerPraticiensAction;
use toubilib\adapters\http\actions\ObtenirAgendaPraticienAction;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;

return [
    AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    HonererRendezVousAction::class => \DI\create(HonererRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    IgnererRendezVousAction::class => \DI\create(IgnererRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    ConsulterRendezVousAction::class => \DI\create(ConsulterRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    CreerRendezVousAction::class => \DI\create(CreerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    ListerCreneauxPraticienAction::class => \DI\create(ListerCreneauxPraticienAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    ObtenirAgendaPraticienAction::class => \DI\create(ObtenirAgendaPraticienAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    ListerPraticiensAction::class => \DI\create(ListerPraticiensAction::class)
        ->constructor(\DI\get(PraticienServiceInterface::class)),

    ConsulterPraticienAction::class => \DI\create(ConsulterPraticienAction::class)
        ->constructor(\DI\get(PraticienServiceInterface::class)),
];
