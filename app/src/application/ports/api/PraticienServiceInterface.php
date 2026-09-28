<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PraticienDTO;

interface PraticienServiceInterface
{
    /**
     * @return PraticienDTO[]
     */
    public function lister(): array;

    public function consulter(string $id): PraticienDTO;
}
