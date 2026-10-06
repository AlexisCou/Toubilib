<?php

declare(strict_types=1);

use toubilib\domain\entities\MotifVisite;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use toubilib\domain\exceptions\CreneauIndisponibleException;
use toubilib\domain\exceptions\HoraireNonDisponibleException;

function praticienDeTestDomaine(string $id = 'praticien-1'): Praticien
{
    return Praticien::reconstituer(
        id: $id,
        nom: 'Dupont',
        prenom: 'Julie',
        titre: 'Dr.',
        specialite: 'Médecine générale',
        ville: 'Nancy',
        email: 'julie.dupont@toubilib.fr',
        telephone: '0600000000',
        nouveauPatient: true,
    );
}

test('dureeRdv renvoie la durée associée au motif', function () {
    $praticien = praticienDeTestDomaine();

    expect($praticien->dureeRdv(MotifVisite::CONSULTATION_INITIALE))->toBe(30)
        ->and($praticien->dureeRdv(MotifVisite::CONSULTATION))->toBe(20)
        ->and($praticien->dureeRdv(MotifVisite::CONSULTATION_SUIVI))->toBe(15);
});

test('verifierHoraireAcceptable accepte un horaire ouvrable', function () {
    $praticien = praticienDeTestDomaine();

    $praticien->verifierHoraireAcceptable(new DateTimeImmutable('2026-09-08 10:00:00')); // mardi 10h

    expect(true)->toBeTrue();
});

test('verifierHoraireAcceptable refuse un jour non ouvrable', function () {
    $praticien = praticienDeTestDomaine();

    expect(fn () => $praticien->verifierHoraireAcceptable(new DateTimeImmutable('2026-09-06 10:00:00'))) // dimanche
        ->toThrow(HoraireNonDisponibleException::class);
});

test('verifierHoraireAcceptable refuse une heure en dehors de la plage acceptée', function () {
    $praticien = praticienDeTestDomaine();

    expect(fn () => $praticien->verifierHoraireAcceptable(new DateTimeImmutable('2026-09-08 20:00:00'))) // mardi 20h
        ->toThrow(HoraireNonDisponibleException::class);
});

test('verifierDisponibilite ne lève rien si l’agenda est vide', function () {
    $praticien = praticienDeTestDomaine();

    $praticien->verifierDisponibilite(new DateTimeImmutable('2026-09-08 10:00:00'), new DateTimeImmutable('2026-09-08 10:20:00'));

    expect(true)->toBeTrue();
});

test('verifierDisponibilite lève une exception en cas de chevauchement', function () {
    $praticien = praticienDeTestDomaine();
    $rdvExistant = RendezVous::creer(
        'rdv-existant',
        $praticien->id(),
        'patient-1',
        new DateTimeImmutable('2026-09-08 10:00:00'),
        MotifVisite::CONSULTATION,
        new DateTimeImmutable(),
    );
    $praticien->chargerAgenda([$rdvExistant]);

    expect(fn () => $praticien->verifierDisponibilite(new DateTimeImmutable('2026-09-08 10:10:00'), new DateTimeImmutable('2026-09-08 10:30:00')))
        ->toThrow(CreneauIndisponibleException::class);
});

test('verifierDisponibilite ignore les RDV annulés', function () {
    $praticien = praticienDeTestDomaine();
    $rdvAnnule = RendezVous::creer(
        'rdv-annule',
        $praticien->id(),
        'patient-1',
        new DateTimeImmutable('2026-09-08 10:00:00'),
        MotifVisite::CONSULTATION,
        new DateTimeImmutable('2026-09-01 08:00:00'),
    );
    $rdvAnnule->annuler(new DateTimeImmutable('2026-09-02 08:00:00'));
    $praticien->chargerAgenda([$rdvAnnule]);

    $praticien->verifierDisponibilite(new DateTimeImmutable('2026-09-08 10:00:00'), new DateTimeImmutable('2026-09-08 10:20:00'));

    expect($rdvAnnule->statut())->toBe(StatutRendezVous::ANNULE);
});
