<?php

declare(strict_types=1);

namespace toubilib\domain\entities;

use DateTimeImmutable;

final class Patient
{
    private function __construct(
        private readonly string $id,
        private readonly string $nom,
        private readonly string $prenom,
        private readonly ?DateTimeImmutable $dateNaissance,
        private readonly ?string $adresse,
        private readonly ?string $ville,
        private readonly ?string $codePostal,
        private readonly ?string $email,
        private readonly string $telephone,
    ) {
    }

    public static function reconstituer(
        string $id,
        string $nom,
        string $prenom,
        ?DateTimeImmutable $dateNaissance,
        ?string $adresse,
        ?string $ville,
        ?string $codePostal,
        ?string $email,
        string $telephone,
    ): self {
        return new self(
            $id,
            $nom,
            $prenom,
            $dateNaissance,
            $adresse,
            $ville,
            $codePostal,
            $email,
            $telephone,
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

    public function dateNaissance(): ?DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function adresse(): ?string
    {
        return $this->adresse;
    }

    public function ville(): ?string
    {
        return $this->ville;
    }

    public function codePostal(): ?string
    {
        return $this->codePostal;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function telephone(): string
    {
        return $this->telephone;
    }
}
