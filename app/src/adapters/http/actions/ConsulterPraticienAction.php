<?php

declare(strict_types=1);

namespace toubilib\adapters\http\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\PraticienServiceInterface;

final class ConsulterPraticienAction
{
    public function __construct(private readonly PraticienServiceInterface $praticienService)
    {
    }

    public function __invoke(Request $request, Response $response, string $id): Response
    {
        try {
            $praticienDTO = $this->praticienService->consulter($id);
        } catch (PraticienIntrouvableException $exception) {
            return $this->erreurJson($response, 404, $exception->getMessage());
        }

        $response->getBody()->write(json_encode($praticienDTO->versTableau(), JSON_THROW_ON_ERROR));

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
