<?php

declare(strict_types=1);

namespace toubilib\adapters\http\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\DonneesRdvInvalidesException;
use toubilib\application\exceptions\PatientIntrouvableException;
use toubilib\application\exceptions\PraticienIntrouvableException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\CreneauIndisponibleException;
use toubilib\domain\exceptions\HoraireNonDisponibleException;

final class CreerRendezVousAction
{
    public function __construct(private readonly ServiceRendezVousInterface $serviceRendezVous)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $donnees = (array) ($request->getParsedBody() ?? []);

        try {
            $dto = CreateRdvDTO::depuisRequete($donnees);
        } catch (DonneesRdvInvalidesException $exception) {
            return $this->erreurJson($response, 422, $exception->getMessage());
        }

        try {
            $rendezVousDTO = $this->serviceRendezVous->creer($dto);
        } catch (PraticienIntrouvableException|PatientIntrouvableException $exception) {
            return $this->erreurJson($response, 404, $exception->getMessage());
        } catch (HoraireNonDisponibleException $exception) {
            return $this->erreurJson($response, 422, $exception->getMessage());
        } catch (CreneauIndisponibleException $exception) {
            return $this->erreurJson($response, 409, $exception->getMessage());
        }

        $response->getBody()->write(json_encode($rendezVousDTO->versTableau(), JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Location', sprintf('/rdv/%s', $rendezVousDTO->id))
            ->withStatus(201);
    }

    private function erreurJson(Response $response, int $statut, string $message): Response
    {
        $response->getBody()->write(json_encode(['erreur' => $message], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statut);
    }
}
