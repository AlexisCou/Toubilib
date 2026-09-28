<?php

declare(strict_types=1);

namespace toubilib\domain\entities;

final class Praticien
{
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
