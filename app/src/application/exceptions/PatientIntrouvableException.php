<?php

declare(strict_types=1);

namespace toubilib\application\exceptions;

use RuntimeException;

final class PatientIntrouvableException extends RuntimeException
{
    public static function pourId(string $id): self
    {
        return new self(sprintf("Aucun patient trouvé pour l'identifiant %s.", $id));
    }
}
