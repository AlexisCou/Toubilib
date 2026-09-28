<?php

declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Praticien;

final class PraticienDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $titre,
        public readonly string $specialite,
        public readonly string $ville,
        public readonly string $email,
        public readonly string $telephone,
        public readonly bool $nouveauPatient,
    ) {
    }

    public static function depuisEntite(Praticien $praticien): self
    {
        return new self(
            id: $praticien->id(),
            nom: $praticien->nom(),
            prenom: $praticien->prenom(),
            titre: $praticien->titre(),
            specialite: $praticien->specialite(),
            ville: $praticien->ville(),
            email: $praticien->email(),
            telephone: $praticien->telephone(),
            nouveauPatient: $praticien->nouveauPatient(),
        );
    }

    public function versTableau(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'titre' => $this->titre,
            'specialite' => $this->specialite,
            'ville' => $this->ville,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'nouveau_patient' => $this->nouveauPatient,
        ];
    }
}
