<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\domain\exceptions\RendezVousDejaAnnuleException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;

interface ServiceRendezVousInterface
{
    public function annuler(string $id): RendezVousDTO;
}
