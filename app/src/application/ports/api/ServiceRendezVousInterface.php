<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use DateTimeImmutable;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\PatientIntrouvableException;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\domain\exceptions\CreneauIndisponibleException;
use toubilib\domain\exceptions\HoraireNonDisponibleException;
use toubilib\domain\exceptions\RendezVousDejaAnnuleException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;
use toubilib\domain\exceptions\RendezVousStatutInvalideException;

interface ServiceRendezVousInterface
{
    /**
     * @throws RendezVousIntrouvableException
     * @throws RendezVousDejaAnnuleException
     * @throws RendezVousDejaPasseException
     */
    public function annuler(string $id): RendezVousDTO;

    /**
     * @throws RendezVousIntrouvableException
     * @throws RendezVousStatutInvalideException le RDV n'est pas à l'état CREE
     */
    public function honorer(string $id): RendezVousDTO;

    /**
     * @throws RendezVousIntrouvableException
     * @throws RendezVousStatutInvalideException le RDV n'est pas à l'état CREE
     */
    public function ignorer(string $id): RendezVousDTO;

    /**
     * @throws RendezVousIntrouvableException
     */
    public function consulter(string $id): RendezVousDTO;

    /**
     * @return RendezVousDTO[]
     * @throws PraticienIntrouvableException
     */
    public function listerCreneauxOccupes(string $praticienId): array;

    /**
     * @return RendezVousDTO[]
     * @throws PraticienIntrouvableException
     */
    public function obtenirAgenda(string $praticienId, DateTimeImmutable $debut, DateTimeImmutable $fin): array;

    /**
     * @throws PraticienIntrouvableException le praticien indiqué n'existe pas
     * @throws PatientIntrouvableException le patient indiqué n'existe pas
     * @throws HoraireNonDisponibleException le jour/l'heure demandés ne sont pas ouvrables pour le praticien
     * @throws CreneauIndisponibleException le praticien a déjà un RDV sur ce créneau
     */
    public function creer(CreateRdvDTO $dto): RendezVousDTO;
}
