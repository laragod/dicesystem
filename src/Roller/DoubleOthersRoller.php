<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Roller;

use Laragod\DiceSystem\Contract\DiceRollerInterface;
use Laragod\DiceSystem\Contract\RandomSourceInterface;
use Laragod\DiceSystem\Support\Base2d6;
use Laragod\DiceSystem\Support\WeightedPicker;

class DoubleOthersRoller implements DiceRollerInterface
{
    protected ?int $last = null;

    public function __construct(
        protected RandomSourceInterface $rng,
        protected WeightedPicker $picker = new WeightedPicker(),
        protected float $multiplier = 2.0, // “double others”
    ) {}

    public function roll(): int
    {
        $base = Base2d6::weights();
        $weights = [];

        foreach ($base as $n => $w) {
            if ($this->last === null) {
                $weights[$n] = (float) $w;
            } else {
                $weights[$n] = ($n === $this->last) ? (float) $w : (float) $w * $this->multiplier;
            }
        }

        $roll = $this->picker->pick($this->rng, $weights);
        $this->last = $roll;

        return $roll;
    }
}
