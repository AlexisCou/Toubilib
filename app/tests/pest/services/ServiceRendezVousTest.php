<?php

declare(strict_types=1);

use tests\pest\fakes\RendezVousRepositoryFake;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\domain\entities\MotifVisite;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use toubilib\domain\exceptions\RendezVousDejaPasseException;

test('annuler un RDV existant et futur met à jour son statut via le repository', function () {
    $depot = new RendezVousRepositoryFake();
    $maintenant = new DateTimeImmutable();
    $debut = $maintenant->modify('+1 day');

    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION, $maintenant);
    $depot->ajouter($rdv);

    $service = new ServiceRendezVous($depot);
    $dto = $service->annuler('rdv-1');

    expect($dto->statut)->toBe(StatutRendezVous::ANNULE->name)
        ->and($depot->find('rdv-1')->statut())->toBe(StatutRendezVous::ANNULE);
});

test('annuler un RDV inconnu lève une exception dédiée', function () {
    $service = new ServiceRendezVous(new RendezVousRepositoryFake());

    expect(fn () => $service->annuler('rdv-inconnu'))
        ->toThrow(RendezVousIntrouvableException::class);
});

test('annuler un RDV déjà passé propage l’erreur du domaine', function () {
    $depot = new RendezVousRepositoryFake();
    $creation = new DateTimeImmutable('2026-08-01 10:00:00');
    $debut = new DateTimeImmutable('2026-08-10 09:00:00'); 
    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION, $creation);
    $depot->ajouter($rdv);

    $service = new ServiceRendezVous($depot);

    expect(fn () => $service->annuler('rdv-1'))
        ->toThrow(RendezVousDejaPasseException::class);
});
