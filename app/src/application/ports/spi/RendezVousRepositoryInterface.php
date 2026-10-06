<?php

declare(strict_types=1);

namespace toubilib\application\ports\spi;

use DateTimeImmutable;
use toubilib\domain\entities\RendezVous;

interface RendezVousRepositoryInterface
{
    public function find(string $id): ?RendezVous;

    public function save(RendezVous $rendezVous): void;

    /**
     * Renvoie les RDV connus d'un praticien pour la journée contenant $jour (utilisé pour
     * vérifier sa disponibilité avant de créer un nouveau RDV).
     *
     * @return RendezVous[]
     */
    public function findParPraticienEtJour(string $praticienId, DateTimeImmutable $jour): array;
}
