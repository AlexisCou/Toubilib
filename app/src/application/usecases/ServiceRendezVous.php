<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\PatientIntrouvableException;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\CreateRdvValidator;
use toubilib\domain\entities\MotifVisite;
use toubilib\domain\entities\RendezVous;

final class ServiceRendezVous implements ServiceRendezVousInterface
{
    public function __construct(
        private readonly RendezVousRepositoryInterface $rendezVousRepository,
        private readonly PraticienRepositoryInterface $praticienRepository,
        private readonly PatientRepositoryInterface $patientRepository,
        private readonly CreateRdvValidator $createRdvValidator,
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

    public function consulter(string $id): RendezVousDTO
    {
        $rendezVous = $this->rendezVousRepository->find($id);

        if ($rendezVous === null) {
            throw RendezVousIntrouvableException::pourId($id);
        }

        return RendezVousDTO::depuisEntite($rendezVous);
    }

    public function creer(CreateRdvDTO $dto): RendezVousDTO
    {
        $this->createRdvValidator->valider($dto);

        $praticien = $this->praticienRepository->find($dto->praticienId);
        if ($praticien === null) {
            throw PraticienIntrouvableException::pourId($dto->praticienId);
        }

        $patient = $this->patientRepository->find($dto->patientId);
        if ($patient === null) {
            throw PatientIntrouvableException::pourId($dto->patientId);
        }

        $agendaDuJour = $this->rendezVousRepository->findParPraticienEtJour($praticien->id(), $dto->dateHeureDebut);
        $praticien->chargerAgenda($agendaDuJour);

        $rendezVous = RendezVous::validerEtCreer(
            Uuid::uuid4()->toString(),
            $praticien,
            $patient,
            $dto->dateHeureDebut,
            MotifVisite::from($dto->motifVisite),
            new DateTimeImmutable(),
        );

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::depuisEntite($rendezVous);
    }
}
