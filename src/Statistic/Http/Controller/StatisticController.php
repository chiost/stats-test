<?php

namespace App\Statistic\Http\Controller;

use App\Statistic\StatisticService;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

        if (!$country || !$this->validateCountryCode($country)) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid country code'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->service->updateCountry(strtolower($country));
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $response->getBody()->write(json_encode([
                'error' => 'Internal server error'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        return $response->withStatus(201);
    }

    /**
     * This method checks if string have 2 letters
     * and checks if they in range of A-Z or a-z by ASCII table
     *
     * @param string $country
     * @return bool
     */
    private function validateCountryCode(string $country): bool
    {
        if (strlen($country) !== 2) return false;

        $a = ord($country[0]);
        $b = ord($country[1]);

        return (($a >= 65 && $a <= 90) || ($a >= 97 && $a <= 122)) &&
            (($b >= 65 && $b <= 90) || ($b >= 97 && $b <= 122));
    }
}