<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Contract;

interface SeedStoreInterface
{
    public function get(string $scope): ?int;

    public function put(string $scope, int $seed): void;

    public function forget(string $scope): void;
}
