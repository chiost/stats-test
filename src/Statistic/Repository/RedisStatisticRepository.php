<?php

namespace App\Statistic\Repository;

use App\Statistic\Shared\Statistic;
use Predis\Client;
use RedisException;

class RedisStatisticRepository implements StatisticRepositoryInterface
{
    private const STATISTIC_KEY_PREFIX = 'statistic:';

    public function __construct(
        private readonly Client $redis
    )
    {
    }

    /**
     * @return Statistic[]
     * @throws RedisException
     */
    public function getStatistic(): array
    {
        $statistic = [];
        foreach ($this->redis->keys(self::STATISTIC_KEY_PREFIX . '*') as $key) {
            $statistic[] = new Statistic(
                mb_substr($key, mb_strlen(self::STATISTIC_KEY_PREFIX)),
                $this->redis->get($key)
            );
        }

        return $statistic;
    }

    /**
     * @throws RedisException
     */
    public function updateCountry(string $country): void
    {
        $this->redis->incr(self::STATISTIC_KEY_PREFIX . $country);
    }
}