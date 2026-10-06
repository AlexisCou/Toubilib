<?php

declare(strict_types=1);

namespace toubilib\domain\entities;

use DateTimeImmutable;
use toubilib\domain\exceptions\CreneauIndisponibleException;
use toubilib\domain\exceptions\HoraireNonDisponibleException;

final class Praticien
{
    private const int HEURE_OUVERTURE = 8;
    private const int HEURE_FERMETURE = 19;
    private const int JOUR_OUVRABLE_MIN = 1; // lundi
    private const int JOUR_OUVRABLE_MAX = 5; // vendredi

    /**
     * @var RendezVous[]
     */
    private array $agenda = [];

    private function __construct(
        private readonly string $id,
        private readonly string $nom,
        private readonly string $prenom,
        private readonly string $titre,
        private readonly string $specialite,
        private readonly string $ville,
        private readonly string $email,
        private readonly string $telephone,
        private readonly bool $nouveauPatient,
    ) {
    }

    public static function reconstituer(
        string $id,
        string $nom,
        string $prenom,
        string $titre,
        string $specialite,
        string $ville,
        string $email,
        string $telephone,
        bool $nouveauPatient,
    ): self {
        return new self(
            $id,
            $nom,
            $prenom,
            $titre,
            $specialite,
            $ville,
            $email,
            $telephone,
            $nouveauPatient,
        );
    }

    /**
     * Charge l'agenda (une partie ou la totalité des RDV) du praticien, nécessaire pour
     * les vérifications de disponibilité. Ce n'est pas la responsabilité de l'entité de
     * savoir comment ces RDV sont obtenus (c'est le rôle du repository / service).
     *
     * @param RendezVous[] $rendezVous
     */
    public function chargerAgenda(array $rendezVous): void
    {
        $this->agenda = $rendezVous;
    }

    /**
     * Simplification du projet : tous les praticiens acceptent les mêmes motifs de visite
     * (cf. sujet). Cette méthode existe pour permettre, à terme, une liste de motifs propre
     * à chaque praticien sans changer l'appelant.
     */
    public function verifierMotifAccepte(MotifVisite $motif): void
    {
        // Aucune restriction supplémentaire dans cette version du projet.
    }

    public function dureeRdv(MotifVisite $motif): int
    {
        return $motif->dureeEnMinutes();
    }

    public function verifierHoraireAcceptable(DateTimeImmutable $dateHeureDebut): void
    {
        $jourSemaine = (int) $dateHeureDebut->format('N');

        if ($jourSemaine < self::JOUR_OUVRABLE_MIN || $jourSemaine > self::JOUR_OUVRABLE_MAX) {
            throw new HoraireNonDisponibleException(
                sprintf('Le %s n\'est pas un jour ouvrable.', $dateHeureDebut->format('d/m/Y'))
            );
        }

        $heure = (int) $dateHeureDebut->format('H');

        if ($heure < self::HEURE_OUVERTURE || $heure >= self::HEURE_FERMETURE) {
            throw new HoraireNonDisponibleException(
                sprintf(
                    'Le praticien %s n\'accepte les RDV qu\'entre %dh et %dh.',
                    $this->id,
                    self::HEURE_OUVERTURE,
                    self::HEURE_FERMETURE,
                )
            );
        }
    }

    public function verifierDisponibilite(DateTimeImmutable $dateHeureDebut, DateTimeImmutable $dateHeureFin): void
    {
        foreach ($this->agenda as $rendezVous) {
            if ($rendezVous->statut() === StatutRendezVous::ANNULE) {
                continue;
            }

            if ($rendezVous->chevauche($dateHeureDebut, $dateHeureFin)) {
                throw new CreneauIndisponibleException(
                    sprintf('Le praticien %s n\'est pas disponible sur ce créneau.', $this->id)
                );
            }
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function nom(): string
    {
        return $this->nom;
    }

    public function prenom(): string
    {
        return $this->prenom;
    }

    public function titre(): string
    {
        return $this->titre;
    }

    public function specialite(): string
    {
        return $this->specialite;
    }

    public function ville(): string
    {
        return $this->ville;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function telephone(): string
    {
        return $this->telephone;
    }

    public function nouveauPatient(): bool
    {
        return $this->nouveauPatient;
    }
}
