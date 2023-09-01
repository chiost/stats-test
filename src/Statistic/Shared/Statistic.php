<?php

namespace App\Statistic\Shared;

class Statistic
{
    public function __construct(
        public string $country,
        public int $visits
    )
    {
    }
}