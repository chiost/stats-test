<?php

namespace App\Statistic\Repository;

use Predis\Client;
use RedisException;

class RedisStatisticRepository implements StatisticRepositoryInterface
{
    const APPROX_COUNTRIES_COUNT = 7;

    private const STATISTIC_KEY_PREFIX = 'statistic:';

    public function __construct(
        private readonly Client $redis
    )
    {
    }

    /**
     * @throws RedisException
     */
    public function getStatistic(): array
    {
        $statistic = [];
        $cursor = 0;
        $prefixLength = strlen(self::STATISTIC_KEY_PREFIX);
        $keysList = [];

        do {
            // In case with active writing scan shows better performance than KEYS
            list($cursor, $keys) = $this->redis->scan(
                $cursor,
                'MATCH',
                self::STATISTIC_KEY_PREFIX . '*',
                'COUNT',
                //We don't have a lot of countries, so we can get all of them in one iteration
                self::APPROX_COUNTRIES_COUNT
            );

            if ($keys) {
                // Merging by spread operator is faster than array_merge
                $keysList = [...$keysList, ...$keys];
            }

        } while ($cursor != 0);


        if ($keysList) {
            $values = $this->redis->mget($keysList);
            foreach ($keysList as $index => $key) {
                $formattedKey = substr($key, $prefixLength);
                $statistic[$formattedKey] = $values[$index];
            }
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