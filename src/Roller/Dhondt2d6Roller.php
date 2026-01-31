<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Roller;

use RuntimeException;
use Laragod\DiceSystem\Contract\DiceRollerInterface;
use Laragod\DiceSystem\Contract\RandomSourceInterface;
use Laragod\DiceSystem\Contract\ResettableInterface;
use Laragod\DiceSystem\Support\Base2d6;
use Laragod\DiceSystem\Support\WeightedPicker;

/**
 * “Flattening” d’Hondt-like:
 * weight(n) = base(n) / (1 + count_in_window(n))
 *
 * windowSize:
 * - null => never resets (keeps balancing forever)
 * - N    => resets every N rolls (e.g. 200 per round)
 */
class Dhondt2d6Roller implements DiceRollerInterface, ResettableInterface
{
    /** @var array<int,int> */
    protected array $counts = [];

    protected int $rollsInWindow = 0;

    public function __construct(
        protected RandomSourceInterface $rng,
        protected WeightedPicker $picker = new WeightedPicker(),
        protected ?int $windowSize = 200,
    ) {
        if ($this->windowSize !== null && $this->windowSize < 1) {
            throw new RuntimeException('windowSize must be null or >= 1');
        }
        $this->reset();
    }

    public function roll(): int
    {
        if ($this->windowSize !== null && $this->rollsInWindow >= $this->windowSize) {
            $this->reset();
        }

        $base = Base2d6::weights();
        $weights = [];

        foreach ($base as $n => $w) {
            $c = $this->counts[$n] ?? 0;
            $weights[$n] = $w / (1 + $c);
        }

        $roll = $this->picker->pick($this->rng, $weights);

        $this->counts[$roll] = ($this->counts[$roll] ?? 0) + 1;
        $this->rollsInWindow++;

        return $roll;
    }

    public function reset(): void
    {
        $this->counts = [];
        foreach (Base2d6::weights() as $n => $_) {
            $this->counts[$n] = 0;
        }
        $this->rollsInWindow = 0;
    }
}
