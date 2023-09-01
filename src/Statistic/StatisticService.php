<?php

namespace App\Statistic;

use App\Statistic\Repository\StatisticRepositoryInterface;

readonly class StatisticService
{
    public function __construct(
        private StatisticRepositoryInterface $repository
    )
    {
    }

    public function getStatistic(): array
    {
        return $this->repository->getStatistic();
    }

    public function updateCountry(string $country): void
    {
        $this->repository->updateCountry($country);
    }
}