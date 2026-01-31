<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Seed;

use Laragod\DiceSystem\Contract\SeedProviderInterface;

class RandomSeedProvider implements SeedProviderInterface
{
    public function __construct(
        protected bool $enabled = true
    ) {}

    public function seedFor(string $scope): ?int
    {
        if (!$this->enabled) {
            return null;
        }
        return random_int(PHP_INT_MIN, PHP_INT_MAX);
    }
}
