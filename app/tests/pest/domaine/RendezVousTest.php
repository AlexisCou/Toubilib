<?php

declare(strict_types=1);

use toubilib\domain\entities\MotifVisite;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use toubilib\domain\exceptions\CreneauIndisponibleException;
use toubilib\domain\exceptions\HoraireNonDisponibleException;
use toubilib\domain\exceptions\RendezVousDejaAnnuleException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;

function praticienDeTestPourRdv(string $id = 'praticien-1'): Praticien
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

function patientDeTestPourRdv(string $id = 'patient-1'): Patient
{
    return Patient::reconstituer(
        id: $id,
        nom: 'Martin',
        prenom: 'Paul',
        dateNaissance: null,
        adresse: null,
        ville: null,
        codePostal: null,
        email: null,
        telephone: '0700000000',
    );
}

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

test('chevauche détecte une intersection entre deux créneaux', function () {
    $rdv = RendezVous::creer(
        'rdv-1',
        'praticien-1',
        'patient-1',
        new DateTimeImmutable('2026-09-10 09:00:00'),
        MotifVisite::CONSULTATION, // 20mn -> 09:00-09:20
        new DateTimeImmutable('2026-09-01 10:00:00'),
    );

    expect($rdv->chevauche(new DateTimeImmutable('2026-09-10 09:10:00'), new DateTimeImmutable('2026-09-10 09:30:00')))->toBeTrue()
        ->and($rdv->chevauche(new DateTimeImmutable('2026-09-10 09:20:00'), new DateTimeImmutable('2026-09-10 09:40:00')))->toBeFalse()
        ->and($rdv->chevauche(new DateTimeImmutable('2026-09-10 08:30:00'), new DateTimeImmutable('2026-09-10 09:00:00')))->toBeFalse();
});

test('validerEtCreer crée un RDV quand le créneau est libre et l’horaire ouvrable', function () {
    $praticien = praticienDeTestPourRdv();
    $patient = patientDeTestPourRdv();
    $debut = new DateTimeImmutable('2026-09-08 10:00:00'); // mardi, 10h

    $rdv = RendezVous::validerEtCreer('rdv-1', $praticien, $patient, $debut, MotifVisite::CONSULTATION, new DateTimeImmutable());

    expect($rdv->praticienId())->toBe($praticien->id())
        ->and($rdv->patientId())->toBe($patient->id())
        ->and($rdv->statut())->toBe(StatutRendezVous::CREE);
});

test('validerEtCreer refuse un horaire non ouvrable (week-end)', function () {
    $praticien = praticienDeTestPourRdv();
    $patient = patientDeTestPourRdv();
    $debut = new DateTimeImmutable('2026-09-06 10:00:00'); // dimanche

    expect(fn () => RendezVous::validerEtCreer('rdv-1', $praticien, $patient, $debut, MotifVisite::CONSULTATION, new DateTimeImmutable()))
        ->toThrow(HoraireNonDisponibleException::class);
});

test('validerEtCreer refuse un créneau déjà occupé', function () {
    $praticien = praticienDeTestPourRdv();
    $patient = patientDeTestPourRdv();
    $debut = new DateTimeImmutable('2026-09-08 10:00:00'); // mardi, 10h

    $rdvExistant = RendezVous::creer('rdv-existant', $praticien->id(), 'patient-2', $debut, MotifVisite::CONSULTATION, new DateTimeImmutable());
    $praticien->chargerAgenda([$rdvExistant]);

    expect(fn () => RendezVous::validerEtCreer('rdv-2', $praticien, $patient, $debut, MotifVisite::CONSULTATION, new DateTimeImmutable()))
        ->toThrow(CreneauIndisponibleException::class);
});
