<?php

declare(strict_types=1);

namespace toubilib\adapters\http\actions;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use toubilib\adapters\http\HateoasLinks;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

final class ObtenirAgendaPraticienAction
{
    public function __construct(private readonly ServiceRendezVousInterface $serviceRendezVous)
    {
    }

    public function __invoke(Request $request, Response $response, string $id): Response
    {
        $parametres = $request->getQueryParams();

        if (!isset($parametres['du'], $parametres['au'])) {
            return $this->erreurJson(
                $response,
                400,
                "Les paramètres de requête 'du' et 'au' (format AAAA-MM-JJ) sont obligatoires.",
            );
        }

        try {
            $debut = new DateTimeImmutable((string) $parametres['du']);
            $fin = new DateTimeImmutable((string) $parametres['au']);
        } catch (Throwable) {
            return $this->erreurJson(
                $response,
                400,
                "Les paramètres 'du' et 'au' doivent être des dates valides (format AAAA-MM-JJ).",
            );
        }

        try {
            $rendezVous = $this->serviceRendezVous->obtenirAgenda($id, $debut, $fin);
        } catch (PraticienIntrouvableException $exception) {
            return $this->erreurJson($response, 404, $exception->getMessage());
        }

        $donnees = array_map(
            static function (RendezVousDTO $dto): array {
                $ligne = $dto->versTableau();
                $ligne['links'] = HateoasLinks::pourRendezVous($dto);

                return $ligne;
            },
            $rendezVous,
        );

        $response->getBody()->write(json_encode($donnees, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    private function erreurJson(Response $response, int $statut, string $message): Response
    {
        $response->getBody()->write(json_encode(['erreur' => $message], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statut);
    }
}
