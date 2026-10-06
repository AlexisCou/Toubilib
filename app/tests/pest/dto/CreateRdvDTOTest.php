<?php

declare(strict_types=1);

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\DonneesRdvInvalidesException;

test('depuisRequete construit un DTO valide à partir de données correctes', function () {
    $dto = CreateRdvDTO::depuisRequete([
        'praticien_id' => 'praticien-1',
        'patient_id' => 'patient-1',
        'date_heure_debut' => '2026-09-08T10:00:00+02:00',
        'motif_visite' => 'C0',
    ]);

    expect($dto->praticienId)->toBe('praticien-1')
        ->and($dto->patientId)->toBe('patient-1')
        ->and($dto->motifVisite)->toBe('C0');
});

test('depuisRequete refuse des données incomplètes', function () {
    expect(fn () => CreateRdvDTO::depuisRequete([
        'praticien_id' => 'praticien-1',
        'motif_visite' => 'C0',
    ]))->toThrow(DonneesRdvInvalidesException::class);
});

test('depuisRequete refuse un motif de visite invalide', function () {
    expect(fn () => CreateRdvDTO::depuisRequete([
        'praticien_id' => 'praticien-1',
        'patient_id' => 'patient-1',
        'date_heure_debut' => '2026-09-08T10:00:00+02:00',
        'motif_visite' => 'INEXISTANT',
    ]))->toThrow(DonneesRdvInvalidesException::class);
});

test('depuisRequete refuse une date mal formée', function () {
    expect(fn () => CreateRdvDTO::depuisRequete([
        'praticien_id' => 'praticien-1',
        'patient_id' => 'patient-1',
        'date_heure_debut' => 'pas-une-date',
        'motif_visite' => 'C0',
    ]))->toThrow(DonneesRdvInvalidesException::class);
});
