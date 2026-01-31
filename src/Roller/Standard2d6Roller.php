<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Roller;

use Laragod\DiceSystem\Contract\DiceRollerInterface;
use Laragod\DiceSystem\Contract\RandomSourceInterface;

class Standard2d6Roller implements DiceRollerInterface
{
    public function __construct(protected RandomSourceInterface $rng) {}

    public function roll(): int
    {
        return $this->rng->int(1, 6) + $this->rng->int(1, 6);
    }
}
