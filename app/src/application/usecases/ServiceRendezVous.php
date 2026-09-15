<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use DateTimeImmutable;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;

final class ServiceRendezVous implements ServiceRendezVousInterface
{
    public function __construct(
        private readonly RendezVousRepositoryInterface $rendezVousRepository,
    ) {
    }

    public function annuler(string $id): RendezVousDTO
    {
        $rendezVous = $this->rendezVousRepository->find($id);

        if ($rendezVous === null) {
            throw RendezVousIntrouvableException::pourId($id);
        }

        $rendezVous->annuler(new DateTimeImmutable());

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::depuisEntite($rendezVous);
    }
}
