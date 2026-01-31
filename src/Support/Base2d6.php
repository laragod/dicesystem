<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Support;

class Base2d6
{
    /** @return array<int,int> */
    public static function weights(): array
    {
        return [
            2 => 1,  3 => 2,  4 => 3,  5 => 4,  6 => 5,  7 => 6,
            8 => 5,  9 => 4, 10 => 3, 11 => 2, 12 => 1,
        ];
    }
}
