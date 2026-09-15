<?php

declare(strict_types=1);

use toubilib\domain\entities\MotifVisite;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use toubilib\domain\exceptions\RendezVousDejaAnnuleException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;

test('un RDV créé porte le statut CREE et une durée déterminée par le motif', function () {
    $maintenant = new DateTimeImmutable('2026-09-01 10:00:00');
    $debut = new DateTimeImmutable('2026-09-10 09:00:00');

    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION_INITIALE, $maintenant);

    expect($rdv->statut())->toBe(StatutRendezVous::CREE)
        ->and($rdv->dateHeureFin())->toEqual($debut->modify('+30 minutes'));
});

test('un RDV futur à l’état CREE peut être annulé', function () {
    $maintenant = new DateTimeImmutable('2026-09-01 10:00:00');
    $debut = new DateTimeImmutable('2026-09-10 09:00:00');

    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION, $maintenant);

    $rdv->annuler($maintenant);

    expect($rdv->statut())->toBe(StatutRendezVous::ANNULE);
});

test('un RDV déjà passé ne peut pas être annulé', function () {
    $creation = new DateTimeImmutable('2026-08-01 10:00:00');
    $debut = new DateTimeImmutable('2026-08-10 09:00:00');
    $maintenant = new DateTimeImmutable('2026-09-01 10:00:00'); 
    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION_SUIVI, $creation);

    expect(fn () => $rdv->annuler($maintenant))
        ->toThrow(RendezVousDejaPasseException::class);

        expect($rdv->statut())->toBe(StatutRendezVous::CREE);
});

test('un RDV déjà annulé ne peut pas être annulé une deuxième fois', function () {
    $maintenant = new DateTimeImmutable('2026-09-01 10:00:00');
    $debut = new DateTimeImmutable('2026-09-10 09:00:00');

    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION, $maintenant);
    $rdv->annuler($maintenant);

    expect(fn () => $rdv->annuler($maintenant))
        ->toThrow(RendezVousDejaAnnuleException::class);
});
