<?php

declare(strict_types=1);

namespace toubilib\adapters\persistence;

use DateTimeImmutable;
use PDO;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\MotifVisite;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\StatutRendezVous;

final class RendezVousRepository implements RendezVousRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(string $id): ?RendezVous
    {
        $statement = $this->pdo->prepare(
            'SELECT id, praticien_id, patient_id, date_heure_debut, date_heure_fin,
                    date_creation, motif_visite, status
             FROM rdv
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        $ligne = $statement->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return $this->versEntite($ligne);
    }

    public function save(RendezVous $rendezVous): void
    {
        $existant = $this->pdo->prepare('SELECT 1 FROM rdv WHERE id = :id');
        $existant->execute(['id' => $rendezVous->id()]);

        if ($existant->fetchColumn() !== false) {
            $statement = $this->pdo->prepare(
                'UPDATE rdv
                 SET praticien_id = :praticien_id,
                     patient_id = :patient_id,
                     date_heure_debut = :date_heure_debut,
                     date_heure_fin = :date_heure_fin,
                     date_creation = :date_creation,
                     motif_visite = :motif_visite,
                     status = :status
                 WHERE id = :id'
            );
        } else {
            $statement = $this->pdo->prepare(
                'INSERT INTO rdv
                    (id, praticien_id, patient_id, date_heure_debut, date_heure_fin,
                     date_creation, motif_visite, status)
                 VALUES
                    (:id, :praticien_id, :patient_id, :date_heure_debut, :date_heure_fin,
                     :date_creation, :motif_visite, :status)'
            );
        }

        $statement->execute([
            'id' => $rendezVous->id(),
            'praticien_id' => $rendezVous->praticienId(),
            'patient_id' => $rendezVous->patientId(),
            'date_heure_debut' => $rendezVous->dateHeureDebut()->format('Y-m-d H:i:s'),
            'date_heure_fin' => $rendezVous->dateHeureFin()->format('Y-m-d H:i:s'),
            'date_creation' => $rendezVous->dateCreation()->format('Y-m-d H:i:s'),
            'motif_visite' => $rendezVous->motifVisite()->value,
            'status' => $rendezVous->statut()->value,
        ]);
    }

        private function versEntite(array $ligne): RendezVous
    {
        return RendezVous::reconstituer(
            id: (string) $ligne['id'],
            praticienId: (string) $ligne['praticien_id'],
            patientId: (string) $ligne['patient_id'],
            dateHeureDebut: new DateTimeImmutable((string) $ligne['date_heure_debut']),
            dateHeureFin: new DateTimeImmutable((string) $ligne['date_heure_fin']),
            dateCreation: new DateTimeImmutable((string) $ligne['date_creation']),
            motifVisite: MotifVisite::from((string) $ligne['motif_visite']),
            statut: StatutRendezVous::from((int) $ligne['status']),
        );
    }
}
