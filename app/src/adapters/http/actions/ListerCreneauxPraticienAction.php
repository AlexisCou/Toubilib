<?php

declare(strict_types=1);

namespace toubilib\adapters\http\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\adapters\http\HateoasLinks;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

final class ListerCreneauxPraticienAction
{
    public function __construct(private readonly ServiceRendezVousInterface $serviceRendezVous)
    {
    }

    public function __invoke(Request $request, Response $response, string $id): Response
    {
        try {
            $rendezVous = $this->serviceRendezVous->listerCreneauxOccupes($id);
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
