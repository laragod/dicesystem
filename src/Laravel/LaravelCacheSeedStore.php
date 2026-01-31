<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Laravel;

use Illuminate\Contracts\Cache\Repository;
use Laragod\DiceSystem\Contract\SeedStoreInterface;

class LaravelCacheSeedStore implements SeedStoreInterface
{
    public function __construct(
        protected Repository $cache,
        protected string $prefix = 'dice-system:seed:',
    ) {}

    public function get(string $scope): ?int
    {
        $v = $this->cache->get($this->key($scope));
        return $v === null ? null : (int)$v;
    }

    public function put(string $scope, int $seed): void
    {
        // store forever by default
        $this->cache->forever($this->key($scope), $seed);
    }

    public function forget(string $scope): void
    {
        $this->cache->forget($this->key($scope));
    }

    protected function key(string $scope): string
    {
        return $this->prefix . hash('sha256', $scope);
    }
}
