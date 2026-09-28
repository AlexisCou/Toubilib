<?php

declare(strict_types=1);

namespace toubilib\adapters\persistence;

use PDO;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;

final class PraticienRepository implements PraticienRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT pr.id, pr.nom, pr.prenom, pr.titre, pr.ville, pr.email, pr.telephone,
                    pr.nouveau_patient, s.libelle AS specialite
             FROM praticien pr
             JOIN specialite s ON s.id = pr.specialite_id
             ORDER BY pr.nom, pr.prenom'
        );

        $lignes = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $ligne): Praticien => $this->versEntite($ligne), $lignes);
    }

    public function find(string $id): ?Praticien
    {
        $statement = $this->pdo->prepare(
            'SELECT pr.id, pr.nom, pr.prenom, pr.titre, pr.ville, pr.email, pr.telephone,
                    pr.nouveau_patient, s.libelle AS specialite
             FROM praticien pr
             JOIN specialite s ON s.id = pr.specialite_id
             WHERE pr.id = :id'
        );
        $statement->execute(['id' => $id]);

        $ligne = $statement->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return $this->versEntite($ligne);
    }

    private function versEntite(array $ligne): Praticien
    {
        return Praticien::reconstituer(
            id: (string) $ligne['id'],
            nom: (string) $ligne['nom'],
            prenom: (string) $ligne['prenom'],
            titre: (string) $ligne['titre'],
            specialite: (string) $ligne['specialite'],
            ville: (string) $ligne['ville'],
            email: (string) $ligne['email'],
            telephone: (string) $ligne['telephone'],
            nouveauPatient: $this->versBooleen($ligne['nouveau_patient']),
        );
    }

    private function versBooleen(mixed $valeurBit): bool
    {
        return trim((string) $valeurBit) === '1';
    }
}
