<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Support;

use RuntimeException;
use Laragod\DiceSystem\Contract\RandomSourceInterface;

class WeightedPicker
{
    /**
     * @param array<int, float|int> $weights [outcome => weight]
     */
    public function pick(RandomSourceInterface $rng, array $weights): int
    {
        $sum = 0.0;
        foreach ($weights as $w) {
            $sum += (float) $w;
        }
        if ($sum <= 0) {
            throw new RuntimeException('All weights are zero/negative.');
        }

        $r = $rng->float() * $sum;
        $acc = 0.0;

        foreach ($weights as $outcome => $w) {
            $acc += (float) $w;
            if ($r <= $acc) {
                return (int) $outcome;
            }
        }

        // numeric drift fallback
        return (int) array_key_last($weights);
    }
}
