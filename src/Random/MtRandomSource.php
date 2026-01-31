<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Random;

use Laragod\DiceSystem\Contract\RandomSourceInterface;

class MtRandomSource implements RandomSourceInterface
{
    public function __construct(?int $seed = null)
    {
        if ($seed !== null) {
            mt_srand($seed);
        }
    }

    public function int(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    public function float(): float
    {
        return mt_rand() / mt_getrandmax();
    }
}
