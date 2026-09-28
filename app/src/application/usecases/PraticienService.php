<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\PraticienDTO;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;

final class PraticienService implements PraticienServiceInterface
{
    public function __construct(
        private readonly PraticienRepositoryInterface $praticienRepository,
    ) {
    }

    public function lister(): array
    {
        $praticiens = $this->praticienRepository->findAll();

        return array_map(
            static fn (Praticien $praticien): PraticienDTO => PraticienDTO::depuisEntite($praticien),
            $praticiens,
        );
    }

    public function consulter(string $id): PraticienDTO
    {
        $praticien = $this->praticienRepository->find($id);

        if ($praticien === null) {
            throw PraticienIntrouvableException::pourId($id);
        }

        return PraticienDTO::depuisEntite($praticien);
    }
}
