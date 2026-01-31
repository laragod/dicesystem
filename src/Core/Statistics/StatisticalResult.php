<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Statistics;

/**
 * Value object representing statistical analysis results.
 */
final readonly class StatisticalResult
{
    /**
     * @param float $mean Average value
     * @param float $median Middle value when sorted
     * @param int|float $mode Most frequent value(s)
     * @param float $standardDeviation Measure of spread
     * @param int|float $min Minimum value
     * @param int|float $max Maximum value
     * @param int $range Difference between max and min
     * @param array<int|float, int> $distribution Value => frequency map
     */
    public function __construct(
        public float $mean,
        public float $median,
        public int|float $mode,
        public float $standardDeviation,
        public int|float $min,
        public int|float $max,
        public int $range,
        public array $distribution,
    ) {
    }
}
