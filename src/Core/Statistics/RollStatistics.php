<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Statistics;

use InvalidArgumentException;

/**
 * Statistical analysis class for dice roll results.
 */
final class RollStatistics
{
    /**
     * Analyze an array of values and return statistical results.
     *
     * @param array<int> $values Array of integer values to analyze
     * @return StatisticalResult Complete statistical analysis
     * @throws InvalidArgumentException If values array is empty
     */
    public function analyze(array $values): StatisticalResult
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Cannot analyze empty array');
        }

        $mean = $this->calculateMean($values);
        $distribution = $this->buildDistribution($values);

        return new StatisticalResult(
            mean: $mean,
            median: $this->calculateMedian($values),
            mode: $this->calculateMode($values),
            standardDeviation: $this->calculateStandardDeviation($values, $mean),
            min: min($values),
            max: max($values),
            range: max($values) - min($values),
            distribution: $distribution,
        );
    }

    /**
     * Build a frequency distribution map.
     *
     * @param array<int> $values Array of values
     * @return array<int|float, int> Value => frequency map
     */
    public function distributionMap(array $values): array
    {
        return $this->buildDistribution($values);
    }

    /**
     * Calculate expected uniform distribution for a die.
     *
     * @param int $sides Number of sides on the die
     * @param int $count Number of rolls
     * @return array<int, float> Side => expected frequency
     */
    public function expectedDistribution(int $sides, int $count): array
    {
        $expected = [];
        $expectedFrequency = $count / $sides;

        for ($i = 1; $i <= $sides; $i++) {
            $expected[$i] = $expectedFrequency;
        }

        return $expected;
    }

    /**
     * Perform chi-squared test for distribution fairness.
     *
     * @param array<int|float, int|float> $observed Observed frequencies
     * @param array<int|float, int|float> $expected Expected frequencies
     * @return float Chi-squared statistic value
     */
    public function chiSquaredTest(array $observed, array $expected): float
    {
        $chiSquared = 0.0;

        foreach ($expected as $value => $expectedFreq) {
            $observedFreq = $observed[$value] ?? 0;

            if ($expectedFreq > 0) {
                $chiSquared += (($observedFreq - $expectedFreq) ** 2) / $expectedFreq;
            }
        }

        return $chiSquared;
    }

    /**
     * Calculate mean (average) of values.
     *
     * @param array<int|float> $values
     * @return float
     */
    private function calculateMean(array $values): float
    {
        return array_sum($values) / count($values);
    }

    /**
     * Calculate median (middle value when sorted).
     *
     * @param array<int|float> $values
     * @return float
     */
    private function calculateMedian(array $values): float
    {
        $sorted = $values;
        sort($sorted);
        $count = count($sorted);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 0) {
            // Even count: average of two middle values
            return ($sorted[$middle - 1] + $sorted[$middle]) / 2;
        }

        // Odd count: exact middle value
        return (float) $sorted[$middle];
    }

    /**
     * Calculate mode (most frequent value).
     * If multiple modes exist with same frequency, returns the smallest.
     *
     * @param array<int|float> $values
     * @return int|float
     */
    private function calculateMode(array $values): int|float
    {
        $distribution = $this->buildDistribution($values);
        $maxFrequency = max($distribution);

        // Find all values with max frequency
        $modes = array_keys($distribution, $maxFrequency, true);

        // Return smallest mode
        return min($modes);
    }

    /**
     * Calculate standard deviation.
     *
     * @param array<int|float> $values
     * @param float $mean Pre-calculated mean
     * @return float
     */
    private function calculateStandardDeviation(array $values, float $mean): float
    {
        $squaredDifferences = array_map(
            fn($value) => ($value - $mean) ** 2,
            $values
        );

        $variance = array_sum($squaredDifferences) / count($values);

        return sqrt($variance);
    }

    /**
     * Build frequency distribution map.
     *
     * @param array<int|float> $values
     * @return array<int|float, int> Value => frequency
     */
    private function buildDistribution(array $values): array
    {
        $distribution = [];

        foreach ($values as $value) {
            if (!isset($distribution[$value])) {
                $distribution[$value] = 0;
            }
            $distribution[$value]++;
        }

        ksort($distribution);

        return $distribution;
    }
}
