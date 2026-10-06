<?php

declare(strict_types=1);

namespace tests\pest\fakes;

use DateTimeImmutable;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;

final class RendezVousRepositoryFake implements RendezVousRepositoryInterface
{
    private array $rendezVousParId = [];

    public function ajouter(RendezVous $rendezVous): void
    {
        $this->rendezVousParId[$rendezVous->id()] = $rendezVous;
    }

    public function find(string $id): ?RendezVous
    {
        return $this->rendezVousParId[$id] ?? null;
    }

    public function save(RendezVous $rendezVous): void
    {
        $this->rendezVousParId[$rendezVous->id()] = $rendezVous;
    }

    public function findParPraticienEtJour(string $praticienId, DateTimeImmutable $jour): array
    {
        return array_values(array_filter(
            $this->rendezVousParId,
            static fn (RendezVous $rdv): bool => $rdv->praticienId() === $praticienId
                && $rdv->dateHeureDebut()->format('Y-m-d') === $jour->format('Y-m-d'),
        ));
    }
}
