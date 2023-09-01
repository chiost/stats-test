<?php
namespace App\Statistic\Http\Controller;

use App\Statistic\StatisticService;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Intl\Countries;
use Throwable;

readonly class StatisticController
{
    public function __construct(
        private StatisticService $service,
        private Logger           $logger,
    )
    {
    }

    public function getStatistic(ServerRequestInterface $request, ResponseInterface $response,): ResponseInterface
    {
        $statistic = $this->service->getStatistic();

        $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200)
            ->getBody()
            ->write(json_encode($statistic));

        return $response;
    }

    public function updateCountry(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $country = $args['country'];

        if (!$country || !array_key_exists(strtoupper($country), Countries::getNames())) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid country code'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->service->updateCountry($country);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $response->getBody()->write(json_encode([
                'error' => 'Internal server error'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        return $response->withStatus(201);
    }
}