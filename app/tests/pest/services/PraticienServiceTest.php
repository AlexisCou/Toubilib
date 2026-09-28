<?php

declare(strict_types=1);

use tests\pest\fakes\PraticienRepositoryFake;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\usecases\PraticienService;
use toubilib\domain\entities\Praticien;

function praticienDeTest(string $id = 'praticien-1'): Praticien
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

test('lister renvoie un DTO pour chaque praticien du dépôt', function () {
    $depot = new PraticienRepositoryFake();
    $depot->ajouter(praticienDeTest('praticien-1'));
    $depot->ajouter(praticienDeTest('praticien-2'));

    $service = new PraticienService($depot);
    $dtos = $service->lister();

    expect($dtos)->toHaveCount(2)
        ->and($dtos[0]->id)->toBe('praticien-1')
        ->and($dtos[1]->id)->toBe('praticien-2');
});

test('consulter un praticien existant renvoie ses informations', function () {
    $depot = new PraticienRepositoryFake();
    $depot->ajouter(praticienDeTest('praticien-1'));

    $service = new PraticienService($depot);
    $dto = $service->consulter('praticien-1');

    expect($dto->nom)->toBe('Dupont')
        ->and($dto->specialite)->toBe('Médecine générale');
});

test('consulter un praticien inconnu lève une exception dédiée', function () {
    $service = new PraticienService(new PraticienRepositoryFake());

    expect(fn () => $service->consulter('praticien-inconnu'))
        ->toThrow(PraticienIntrouvableException::class);
});
