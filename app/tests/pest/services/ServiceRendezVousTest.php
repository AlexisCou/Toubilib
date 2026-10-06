<?php

declare(strict_types=1);

use tests\pest\fakes\PatientRepositoryFake;
use tests\pest\fakes\PraticienRepositoryFake;
use tests\pest\fakes\RendezVousRepositoryFake;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\PatientIntrouvableException;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\validators\CreateRdvValidator;
use toubilib\domain\entities\MotifVisite;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;
use toubilib\domain\exceptions\CreneauIndisponibleException;
use toubilib\domain\exceptions\HoraireNonDisponibleException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;

function creerService(
    ?RendezVousRepositoryFake $rendezVousDepot = null,
    ?PraticienRepositoryFake $praticienDepot = null,
    ?PatientRepositoryFake $patientDepot = null,
): ServiceRendezVous {
    $rendezVousDepot ??= new RendezVousRepositoryFake();
    $praticienDepot ??= new PraticienRepositoryFake();
    $patientDepot ??= new PatientRepositoryFake();

    return new ServiceRendezVous(
        $rendezVousDepot,
        $praticienDepot,
        $patientDepot,
        new CreateRdvValidator($praticienDepot, $patientDepot),
    );
}

function praticienPourServiceTest(string $id = 'praticien-1'): Praticien
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

function patientPourServiceTest(string $id = 'patient-1'): Patient
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

test('annuler un RDV existant et futur met à jour son statut via le repository', function () {
    $depot = new RendezVousRepositoryFake();
    $maintenant = new DateTimeImmutable();
    $debut = $maintenant->modify('+1 day');

    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION, $maintenant);
    $depot->ajouter($rdv);

    $service = creerService(rendezVousDepot: $depot);
    $dto = $service->annuler('rdv-1');

    expect($dto->statut)->toBe(StatutRendezVous::ANNULE->name)
        ->and($depot->find('rdv-1')->statut())->toBe(StatutRendezVous::ANNULE);
});

test('annuler un RDV inconnu lève une exception dédiée', function () {
    $service = creerService();

    expect(fn () => $service->annuler('rdv-inconnu'))
        ->toThrow(RendezVousIntrouvableException::class);
});

test('annuler un RDV déjà passé propage l’erreur du domaine', function () {
    $depot = new RendezVousRepositoryFake();
    $creation = new DateTimeImmutable('2026-08-01 10:00:00');
    $debut = new DateTimeImmutable('2026-08-10 09:00:00');
    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION, $creation);
    $depot->ajouter($rdv);

    $service = creerService(rendezVousDepot: $depot);

    expect(fn () => $service->annuler('rdv-1'))
        ->toThrow(RendezVousDejaPasseException::class);
});

test('consulter un RDV existant renvoie ses informations', function () {
    $depot = new RendezVousRepositoryFake();
    $maintenant = new DateTimeImmutable();
    $debut = $maintenant->modify('+1 day');

    $rdv = RendezVous::creer('rdv-1', 'praticien-1', 'patient-1', $debut, MotifVisite::CONSULTATION, $maintenant);
    $depot->ajouter($rdv);

    $service = creerService(rendezVousDepot: $depot);
    $dto = $service->consulter('rdv-1');

    expect($dto->id)->toBe('rdv-1')
        ->and($dto->praticienId)->toBe('praticien-1');
});

test('consulter un RDV inconnu lève une exception dédiée', function () {
    $service = creerService();

    expect(fn () => $service->consulter('rdv-inconnu'))
        ->toThrow(RendezVousIntrouvableException::class);
});

test('creer un RDV valide le praticien, le patient, l’horaire et la disponibilité puis persiste le RDV', function () {
    $praticienDepot = new PraticienRepositoryFake();
    $praticienDepot->ajouter(praticienPourServiceTest());
    $patientDepot = new PatientRepositoryFake();
    $patientDepot->ajouter(patientPourServiceTest());
    $rendezVousDepot = new RendezVousRepositoryFake();

    $service = creerService($rendezVousDepot, $praticienDepot, $patientDepot);

    $dto = new CreateRdvDTO(
        praticienId: 'praticien-1',
        patientId: 'patient-1',
        dateHeureDebut: new DateTimeImmutable('2026-09-08 10:00:00'), // mardi
        motifVisite: MotifVisite::CONSULTATION->value,
    );

    $rendezVousDTO = $service->creer($dto);

    expect($rendezVousDTO->praticienId)->toBe('praticien-1')
        ->and($rendezVousDTO->patientId)->toBe('patient-1')
        ->and($rendezVousDepot->find($rendezVousDTO->id))->not->toBeNull();
});

test('creer un RDV avec un praticien inconnu lève une exception dédiée', function () {
    $patientDepot = new PatientRepositoryFake();
    $patientDepot->ajouter(patientPourServiceTest());

    $service = creerService(patientDepot: $patientDepot);

    $dto = new CreateRdvDTO('praticien-inconnu', 'patient-1', new DateTimeImmutable('2026-09-08 10:00:00'), MotifVisite::CONSULTATION->value);

    expect(fn () => $service->creer($dto))
        ->toThrow(PraticienIntrouvableException::class);
});

test('creer un RDV avec un patient inconnu lève une exception dédiée', function () {
    $praticienDepot = new PraticienRepositoryFake();
    $praticienDepot->ajouter(praticienPourServiceTest());

    $service = creerService(praticienDepot: $praticienDepot);

    $dto = new CreateRdvDTO('praticien-1', 'patient-inconnu', new DateTimeImmutable('2026-09-08 10:00:00'), MotifVisite::CONSULTATION->value);

    expect(fn () => $service->creer($dto))
        ->toThrow(PatientIntrouvableException::class);
});

test('creer un RDV en dehors des horaires ouvrables lève une exception métier', function () {
    $praticienDepot = new PraticienRepositoryFake();
    $praticienDepot->ajouter(praticienPourServiceTest());
    $patientDepot = new PatientRepositoryFake();
    $patientDepot->ajouter(patientPourServiceTest());

    $service = creerService(praticienDepot: $praticienDepot, patientDepot: $patientDepot);

    $dto = new CreateRdvDTO('praticien-1', 'patient-1', new DateTimeImmutable('2026-09-06 10:00:00'), MotifVisite::CONSULTATION->value); // dimanche

    expect(fn () => $service->creer($dto))
        ->toThrow(HoraireNonDisponibleException::class);
});

test('creer un RDV sur un créneau déjà occupé lève une exception métier', function () {
    $praticienDepot = new PraticienRepositoryFake();
    $praticienDepot->ajouter(praticienPourServiceTest());
    $patientDepot = new PatientRepositoryFake();
    $patientDepot->ajouter(patientPourServiceTest());
    $rendezVousDepot = new RendezVousRepositoryFake();
    $rendezVousDepot->ajouter(RendezVous::creer(
        'rdv-existant',
        'praticien-1',
        'patient-1',
        new DateTimeImmutable('2026-09-08 10:00:00'),
        MotifVisite::CONSULTATION,
        new DateTimeImmutable(),
    ));

    $service = creerService($rendezVousDepot, $praticienDepot, $patientDepot);

    $dto = new CreateRdvDTO('praticien-1', 'patient-1', new DateTimeImmutable('2026-09-08 10:10:00'), MotifVisite::CONSULTATION->value);

    expect(fn () => $service->creer($dto))
        ->toThrow(CreneauIndisponibleException::class);
});
