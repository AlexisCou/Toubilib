<?php

declare(strict_types=1);

namespace toubilib\adapters\http\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\ports\api\PraticienServiceInterface;

final class ListerPraticiensAction
{
    public function __construct(private readonly PraticienServiceInterface $praticienService)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $praticiens = $this->praticienService->lister();

        $donnees = array_map(
            static fn (PraticienDTO $dto): array => $dto->versTableau(),
            $praticiens,
        );

        $response->getBody()->write(json_encode($donnees, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
