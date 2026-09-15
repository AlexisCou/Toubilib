<?php

declare(strict_types=1);

namespace toubilib\application\exceptions;

use RuntimeException;

final class RendezVousIntrouvableException extends RuntimeException
{
    public static function pourId(string $id): self
    {
        return new self(sprintf("Aucun rendez-vous trouvé pour l'identifiant %s.", $id));
    }
}
