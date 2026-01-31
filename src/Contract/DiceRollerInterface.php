<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Contract;

interface DiceRollerInterface
{
    public function roll(): int;
}
