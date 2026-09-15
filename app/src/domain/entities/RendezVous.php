<?php

declare(strict_types=1);

namespace toubilib\domain\entities;

use DateTimeImmutable;
use toubilib\domain\exceptions\RendezVousDejaAnnuleException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;

final class RendezVous
{
    private function __construct(
        private readonly string $id,
        private readonly string $praticienId,
        private readonly string $patientId,
        private readonly DateTimeImmutable $dateHeureDebut,
        private readonly DateTimeImmutable $dateHeureFin,
        private readonly DateTimeImmutable $dateCreation,
        private readonly MotifVisite $motifVisite,
        private StatutRendezVous $statut,
    ) {
    }

        public static function reconstituer(
        string $id,
        string $praticienId,
        string $patientId,
        DateTimeImmutable $dateHeureDebut,
        DateTimeImmutable $dateHeureFin,
        DateTimeImmutable $dateCreation,
        MotifVisite $motifVisite,
        StatutRendezVous $statut,
    ): self {
        return new self(
            $id,
            $praticienId,
            $patientId,
            $dateHeureDebut,
            $dateHeureFin,
            $dateCreation,
            $motifVisite,
            $statut,
        );
    }

        public static function creer(
        string $id,
        string $praticienId,
        string $patientId,
        DateTimeImmutable $dateHeureDebut,
        MotifVisite $motifVisite,
        DateTimeImmutable $maintenant,
    ): self {
        $dateHeureFin = $dateHeureDebut->modify(
            sprintf('+%d minutes', $motifVisite->dureeEnMinutes())
        );

        return new self(
            $id,
            $praticienId,
            $patientId,
            $dateHeureDebut,
            $dateHeureFin,
            $maintenant,
            $motifVisite,
            StatutRendezVous::CREE,
        );
    }

        public function annuler(DateTimeImmutable $maintenant): void
    {
        if ($this->statut !== StatutRendezVous::CREE) {
            throw new RendezVousDejaAnnuleException(
                sprintf('Le rendez-vous %s ne peut pas être annulé (statut actuel : %s).', $this->id, $this->statut->name)
            );
        }

        if ($this->dateHeureDebut < $maintenant) {
            throw new RendezVousDejaPasseException(
                sprintf('Le rendez-vous %s est déjà passé, il ne peut plus être annulé.', $this->id)
            );
        }

        $this->statut = StatutRendezVous::ANNULE;
    }

    public function marquerHonore(): void
    {
        $this->statut = StatutRendezVous::HONORE;
    }

    public function marquerIgnore(): void
    {
        $this->statut = StatutRendezVous::IGNORE;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function praticienId(): string
    {
        return $this->praticienId;
    }

    public function patientId(): string
    {
        return $this->patientId;
    }

    public function dateHeureDebut(): DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function dateHeureFin(): DateTimeImmutable
    {
        return $this->dateHeureFin;
    }

    public function dateCreation(): DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function motifVisite(): MotifVisite
    {
        return $this->motifVisite;
    }

    public function statut(): StatutRendezVous
    {
        return $this->statut;
    }
}
