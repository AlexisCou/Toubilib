<?php

declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;

final class PraticienRepositoryFake implements PraticienRepositoryInterface
{
    private array $praticiensParId = [];

    public function ajouter(Praticien $praticien): void
    {
        $this->praticiensParId[$praticien->id()] = $praticien;
    }

    public function findAll(): array
    {
        return array_values($this->praticiensParId);
    }

    public function find(string $id): ?Praticien
    {
        return $this->praticiensParId[$id] ?? null;
    }
}
