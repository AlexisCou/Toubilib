<?php

declare(strict_types=1);

namespace toubilib\application\exceptions;

use RuntimeException;

final class DonneesRdvInvalidesException extends RuntimeException
{
    /**
     * @param string[] $erreurs
     */
    public static function pourErreurs(array $erreurs): self
    {
        return new self(implode(' ', $erreurs));
    }
}
