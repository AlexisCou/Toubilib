<?php

declare(strict_types=1);

namespace toubilib\adapters\http\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\application\exceptions\RendezVousIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

final class ConsulterRendezVousAction
{
    public function __construct(private readonly ServiceRendezVousInterface $serviceRendezVous)
    {
    }

    public function __invoke(Request $request, Response $response, string $id): Response
    {
        try {
            $rendezVousDTO = $this->serviceRendezVous->consulter($id);
        } catch (RendezVousIntrouvableException $exception) {
            return $this->erreurJson($response, 404, $exception->getMessage());
        }

        $response->getBody()->write(json_encode($rendezVousDTO->versTableau(), JSON_THROW_ON_ERROR));

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
