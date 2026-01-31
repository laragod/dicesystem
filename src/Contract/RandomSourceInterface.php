<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Contract;

interface RandomSourceInterface
{
    public function int(int $min, int $max): int;

    /** [0, 1) */
    public function float(): float;
}
