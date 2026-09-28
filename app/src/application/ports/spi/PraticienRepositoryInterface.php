<?php

declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Praticien;

interface PraticienRepositoryInterface
{
    /**
     * @return Praticien[]
     */
    public function findAll(): array;

    public function find(string $id): ?Praticien;
}
