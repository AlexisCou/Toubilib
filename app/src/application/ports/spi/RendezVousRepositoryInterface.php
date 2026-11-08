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

    /**
     * Renvoie les créneaux occupés d'un praticien, c'est-à-dire tous ses RDV dont le statut
     * n'est pas ANNULE (un RDV annulé libère le créneau).
     *
     * @return RendezVous[]
     */
    public function findCreneauxOccupesParPraticien(string $praticienId): array;

    /**
     * Renvoie l'agenda complet d'un praticien sur une période donnée (tous les RDV, quel que
     * soit leur statut, dont le début se situe entre $debut et $fin inclus).
     *
     * @return RendezVous[]
     */
    public function findParPraticienEtPeriode(string $praticienId, DateTimeImmutable $debut, DateTimeImmutable $fin): array;
}
