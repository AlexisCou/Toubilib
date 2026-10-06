<?php

declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;

final class PatientRepositoryFake implements PatientRepositoryInterface
{
    private array $patientsParId = [];

    public function ajouter(Patient $patient): void
    {
        $this->patientsParId[$patient->id()] = $patient;
    }

    public function find(string $id): ?Patient
    {
        return $this->patientsParId[$id] ?? null;
    }
}
