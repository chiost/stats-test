<?php

namespace App\Statistic\Repository;

interface StatisticRepositoryInterface
{
    public function getStatistic(): array;

    public function updateCountry(string $country): void;
}