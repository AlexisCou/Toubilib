<?php

declare(strict_types=1);

namespace toubilib\adapters\persistence;

use DateTimeImmutable;
use PDO;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;

final class PatientRepository implements PatientRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(string $id): ?Patient
    {
        $statement = $this->pdo->prepare(
            'SELECT id, nom, prenom, date_naissance, adresse, ville, code_postal, email, telephone
             FROM patient
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        $ligne = $statement->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return Patient::reconstituer(
            id: (string) $ligne['id'],
            nom: (string) $ligne['nom'],
            prenom: (string) $ligne['prenom'],
            dateNaissance: $ligne['date_naissance'] !== null ? new DateTimeImmutable((string) $ligne['date_naissance']) : null,
            adresse: $ligne['adresse'] !== null ? (string) $ligne['adresse'] : null,
            ville: $ligne['ville'] !== null ? (string) $ligne['ville'] : null,
            codePostal: $ligne['code_postal'] !== null ? (string) $ligne['code_postal'] : null,
            email: $ligne['email'] !== null ? (string) $ligne['email'] : null,
            telephone: (string) $ligne['telephone'],
        );
    }
}
