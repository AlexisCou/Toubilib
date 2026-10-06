<?php

declare(strict_types=1);

namespace toubilib\application\validators;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\PatientIntrouvableException;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

/**
 * Validation applicative des données de création d'un RDV : vérifie que les entités
 * référencées existent. Les règles métier (motif, horaire, disponibilité) relèvent du
 * domaine (entités Praticien / RendezVous), pas de ce validateur.
 */
final class CreateRdvValidator
{
    public function __construct(
        private readonly PraticienRepositoryInterface $praticienRepository,
        private readonly PatientRepositoryInterface $patientRepository,
    ) {
    }

    public function valider(CreateRdvDTO $dto): void
    {
        if ($this->praticienRepository->find($dto->praticienId) === null) {
            throw PraticienIntrouvableException::pourId($dto->praticienId);
        }

        if ($this->patientRepository->find($dto->patientId) === null) {
            throw PatientIntrouvableException::pourId($dto->patientId);
        }
    }
}
