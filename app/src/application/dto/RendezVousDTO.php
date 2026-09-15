<?php

declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

final class RendezVousDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $praticienId,
        public readonly string $patientId,
        public readonly string $dateHeureDebut,
        public readonly string $dateHeureFin,
        public readonly string $dateCreation,
        public readonly string $motifVisite,
        public readonly string $statut,
    ) {
    }

    public static function depuisEntite(RendezVous $rendezVous): self
    {
        return new self(
            id: $rendezVous->id(),
            praticienId: $rendezVous->praticienId(),
            patientId: $rendezVous->patientId(),
            dateHeureDebut: $rendezVous->dateHeureDebut()->format(DATE_ATOM),
            dateHeureFin: $rendezVous->dateHeureFin()->format(DATE_ATOM),
            dateCreation: $rendezVous->dateCreation()->format(DATE_ATOM),
            motifVisite: $rendezVous->motifVisite()->value,
            statut: $rendezVous->statut()->name,
        );
    }

        public function versTableau(): array
    {
        return [
            'id' => $this->id,
            'praticien_id' => $this->praticienId,
            'patient_id' => $this->patientId,
            'date_heure_debut' => $this->dateHeureDebut,
            'date_heure_fin' => $this->dateHeureFin,
            'date_creation' => $this->dateCreation,
            'motif_visite' => $this->motifVisite,
            'statut' => $this->statut,
        ];
    }
}
