<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Contract;

interface SeedProviderInterface
{
    public function seedFor(string $scope): ?int;
}
