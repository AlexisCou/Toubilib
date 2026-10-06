<?php

declare(strict_types=1);

namespace toubilib\application\dto;

use DateTimeImmutable;
use Exception;
use toubilib\application\exceptions\DonneesRdvInvalidesException;
use toubilib\domain\entities\MotifVisite;

final class CreateRdvDTO
{
    public function __construct(
        public readonly string $praticienId,
        public readonly string $patientId,
        public readonly DateTimeImmutable $dateHeureDebut,
        public readonly string $motifVisite,
    ) {
    }

    /**
     * Valide la présence, le type et le format des données brutes reçues dans la requête
     * HTTP (validation "syntaxique"). Les vérifications métier (existence du praticien et
     * du patient, disponibilité...) sont réalisées plus tard, par le validateur applicatif
     * et le domaine.
     */
    public static function depuisRequete(array $donnees): self
    {
        $erreurs = [];

        foreach (['praticien_id', 'patient_id', 'date_heure_debut', 'motif_visite'] as $champ) {
            if (!array_key_exists($champ, $donnees) || $donnees[$champ] === '' || $donnees[$champ] === null) {
                $erreurs[] = sprintf("Le champ '%s' est obligatoire.", $champ);
            }
        }

        if ($erreurs !== []) {
            throw DonneesRdvInvalidesException::pourErreurs($erreurs);
        }

        if (!is_string($donnees['praticien_id']) || !is_string($donnees['patient_id'])) {
            throw DonneesRdvInvalidesException::pourErreurs([
                "Les champs 'praticien_id' et 'patient_id' doivent être des chaînes de caractères.",
            ]);
        }

        if (!is_string($donnees['motif_visite']) || MotifVisite::tryFrom($donnees['motif_visite']) === null) {
            throw DonneesRdvInvalidesException::pourErreurs([
                sprintf(
                    "Le motif de visite '%s' est invalide (valeurs acceptées : %s).",
                    is_string($donnees['motif_visite']) ? $donnees['motif_visite'] : '(type invalide)',
                    implode(', ', array_map(static fn (MotifVisite $motif): string => $motif->value, MotifVisite::cases())),
                ),
            ]);
        }

        if (!is_string($donnees['date_heure_debut'])) {
            throw DonneesRdvInvalidesException::pourErreurs([
                "Le champ 'date_heure_debut' doit être une chaîne de caractères au format ISO 8601.",
            ]);
        }

        try {
            $dateHeureDebut = new DateTimeImmutable($donnees['date_heure_debut']);
        } catch (Exception) {
            throw DonneesRdvInvalidesException::pourErreurs([
                sprintf("La date '%s' est invalide.", $donnees['date_heure_debut']),
            ]);
        }

        return new self(
            praticienId: trim($donnees['praticien_id']),
            patientId: trim($donnees['patient_id']),
            dateHeureDebut: $dateHeureDebut,
            motifVisite: $donnees['motif_visite'],
        );
    }
}
