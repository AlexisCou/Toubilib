<?php

declare(strict_types=1);

namespace toubilib\adapters\http;

use toubilib\application\dto\PraticienDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\domain\entities\StatutRendezVous;

/**
 * Construit les liens HATEOAS ajoutés aux réponses JSON de l'API, afin que le client puisse
 * découvrir les actions disponibles depuis la ressource elle-même, sans les connaître à l'avance.
 */
final class HateoasLinks
{
    public static function pourRendezVous(RendezVousDTO $dto): array
    {
        $liens = [
            'self' => ['href' => sprintf('/rdvs/%s', $dto->id)],
            'praticien' => ['href' => sprintf('/praticiens/%s', $dto->praticienId)],
            'patient' => ['href' => sprintf('/patients/%s', $dto->patientId)],
        ];

        // Les actions de transition d'état ne sont proposées que si le RDV est encore à l'état
        // CREE : un RDV déjà annulé/honoré/ignoré ne peut plus être annulé, honoré ou ignoré.
        if ($dto->statut === StatutRendezVous::CREE->name) {
            $liens['annuler'] = ['href' => sprintf('/rdvs/%s/annuler', $dto->id)];
            $liens['honorer'] = ['href' => sprintf('/rdvs/%s/honorer', $dto->id)];
            $liens['ignorer'] = ['href' => sprintf('/rdvs/%s/ignorer', $dto->id)];
        }

        return $liens;
    }

    public static function pourPraticien(PraticienDTO $dto): array
    {
        return [
            'self' => ['href' => sprintf('/praticiens/%s', $dto->id)],
            'rdvs' => ['href' => sprintf('/praticiens/%s/rdvs', $dto->id)],
            'agenda' => ['href' => sprintf('/praticiens/%s/agenda', $dto->id)],
        ];
    }
}
