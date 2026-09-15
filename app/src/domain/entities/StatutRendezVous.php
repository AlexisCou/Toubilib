<?php

declare(strict_types=1);

namespace toubilib\domain\entities;

enum StatutRendezVous: int
{
    case CREE = 0;
    case ANNULE = 1;
    case HONORE = 2;
    case IGNORE = 3;
}
