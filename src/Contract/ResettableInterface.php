<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Contract;

interface ResettableInterface
{
    public function reset(): void;
}
