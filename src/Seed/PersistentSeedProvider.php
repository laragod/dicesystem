<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Seed;

use Laragod\DiceSystem\Contract\SeedProviderInterface;
use Laragod\DiceSystem\Contract\SeedStoreInterface;

class PersistentSeedProvider implements SeedProviderInterface
{
    public function __construct(
        protected SeedStoreInterface $store,
        protected bool $enabled = true,
    ) {}

    public function seedFor(string $scope): ?int
    {
        if (!$this->enabled) {
            return null; // do not seed global MT
        }

        $existing = $this->store->get($scope);
        if ($existing !== null) {
            return $existing;
        }

        $seed = random_int(PHP_INT_MIN, PHP_INT_MAX);
        $this->store->put($scope, $seed);

        return $seed;
    }

    public function rotate(string $scope): int
    {
        $seed = random_int(PHP_INT_MIN, PHP_INT_MAX);
        $this->store->put($scope, $seed);
        return $seed;
    }

    public function forget(string $scope): void
    {
        $this->store->forget($scope);
    }
}
