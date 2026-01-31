<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Contract;

interface SeedObserverInterface
{
    public function onSeedResolved(string $scope, ?int $seed, array $meta = []): void;
}
