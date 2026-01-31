<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Contract;

interface HasRemainingInterface
{
    public function remaining(): int;
}
