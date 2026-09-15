<?php

declare(strict_types=1);

namespace toubilib\domain\entities;

enum MotifVisite: string
{
    case CONSULTATION_INITIALE = 'CI';
    case CONSULTATION = 'C0';
    case CONSULTATION_SUIVI = 'CS';

    public function dureeEnMinutes(): int
    {
        return match ($this) {
            self::CONSULTATION_INITIALE => 30,
            self::CONSULTATION => 20,
            self::CONSULTATION_SUIVI => 15,
        };
    }
}
