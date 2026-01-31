<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Statistics;

use InvalidArgumentException;
use Laragod\DiceSystem\Core\Statistics\RollStatistics;
use Laragod\DiceSystem\Core\Statistics\StatisticalResult;
use PHPUnit\Framework\TestCase;

final class RollStatisticsTest extends TestCase
{
    private RollStatistics $statistics;

    protected function setUp(): void
    {
        $this->statistics = new RollStatistics();
    }

    public function test_analyze_returns_statistical_result(): void
    {
        $values = [1, 2, 3, 4, 5];

        $result = $this->statistics->analyze($values);

        $this->assertInstanceOf(StatisticalResult::class, $result);
    }

    public function test_mean_calculation_with_known_dataset(): void
    {
        // Mean of [1, 2, 3, 4, 5] = 15/5 = 3.0
        $values = [1, 2, 3, 4, 5];

        $result = $this->statistics->analyze($values);

        $this->assertSame(3.0, $result->mean);
    }

    public function test_mean_calculation_with_larger_dataset(): void
    {
        // Mean of [10, 20, 30, 40, 50] = 150/5 = 30.0
        $values = [10, 20, 30, 40, 50];

        $result = $this->statistics->analyze($values);

        $this->assertSame(30.0, $result->mean);
    }

    public function test_median_with_odd_count(): void
    {
        // Median of [1, 2, 3, 4, 5] = 3
        $values = [1, 2, 3, 4, 5];

        $result = $this->statistics->analyze($values);

        $this->assertSame(3.0, $result->median);
    }

    public function test_median_with_even_count(): void
    {
        // Median of [1, 2, 3, 4] = (2 + 3) / 2 = 2.5
        $values = [1, 2, 3, 4];

        $result = $this->statistics->analyze($values);

        $this->assertSame(2.5, $result->median);
    }

    public function test_median_with_unsorted_array(): void
    {
        // Median of [5, 1, 3, 2, 4] (sorted: [1, 2, 3, 4, 5]) = 3
        $values = [5, 1, 3, 2, 4];

        $result = $this->statistics->analyze($values);

        $this->assertSame(3.0, $result->median);
    }

    public function test_mode_with_single_mode(): void
    {
        // Mode of [1, 2, 2, 3, 4] = 2 (appears twice)
        $values = [1, 2, 2, 3, 4];

        $result = $this->statistics->analyze($values);

        $this->assertSame(2, $result->mode);
    }

    public function test_mode_with_multiple_modes_returns_smallest(): void
    {
        // Both 2 and 4 appear twice, should return 2 (smallest)
        $values = [1, 2, 2, 3, 4, 4];

        $result = $this->statistics->analyze($values);

        $this->assertSame(2, $result->mode);
    }

    public function test_mode_when_all_values_same_frequency(): void
    {
        // All appear once, should return minimum
        $values = [5, 3, 1, 4, 2];

        $result = $this->statistics->analyze($values);

        $this->assertSame(1, $result->mode);
    }

    public function test_standard_deviation_calculation(): void
    {
        // StdDev of [2, 4, 4, 4, 5, 5, 7, 9]
        // Mean = 40/8 = 5
        // Variance = ((2-5)² + (4-5)² + (4-5)² + (4-5)² + (5-5)² + (5-5)² + (7-5)² + (9-5)²) / 8
        // Variance = (9 + 1 + 1 + 1 + 0 + 0 + 4 + 16) / 8 = 32/8 = 4
        // StdDev = sqrt(4) = 2.0
        $values = [2, 4, 4, 4, 5, 5, 7, 9];

        $result = $this->statistics->analyze($values);

        $this->assertEqualsWithDelta(2.0, $result->standardDeviation, 0.0001);
    }

    public function test_standard_deviation_with_no_variance(): void
    {
        // All same values should have stddev = 0
        $values = [5, 5, 5, 5, 5];

        $result = $this->statistics->analyze($values);

        $this->assertSame(0.0, $result->standardDeviation);
    }

    public function test_min_max_and_range(): void
    {
        $values = [3, 7, 2, 9, 1, 5];

        $result = $this->statistics->analyze($values);

        $this->assertSame(1, $result->min);
        $this->assertSame(9, $result->max);
        $this->assertSame(8, $result->range);
    }

    public function test_distribution_map(): void
    {
        $values = [1, 2, 2, 3, 3, 3];

        $result = $this->statistics->analyze($values);

        $expected = [
            1 => 1,
            2 => 2,
            3 => 3,
        ];

        $this->assertSame($expected, $result->distribution);
    }

    public function test_distribution_map_method(): void
    {
        $values = [6, 6, 2, 6, 5, 2];

        $distribution = $this->statistics->distributionMap($values);

        $expected = [
            2 => 2,
            5 => 1,
            6 => 3,
        ];

        $this->assertSame($expected, $distribution);
    }

    public function test_expected_distribution_for_d6(): void
    {
        $expected = $this->statistics->expectedDistribution(6, 60);

        $this->assertCount(6, $expected);
        $this->assertSame(10.0, $expected[1]);
        $this->assertSame(10.0, $expected[6]);
    }

    public function test_expected_distribution_for_d20(): void
    {
        $expected = $this->statistics->expectedDistribution(20, 100);

        $this->assertCount(20, $expected);
        $this->assertSame(5.0, $expected[1]);
        $this->assertSame(5.0, $expected[20]);
    }

    public function test_chi_squared_with_perfectly_uniform_distribution(): void
    {
        // Perfect uniform distribution should have chi-squared = 0
        $observed = [
            1 => 10,
            2 => 10,
            3 => 10,
            4 => 10,
            5 => 10,
            6 => 10,
        ];

        $expected = $this->statistics->expectedDistribution(6, 60);

        $chiSquared = $this->statistics->chiSquaredTest($observed, $expected);

        $this->assertSame(0.0, $chiSquared);
    }

    public function test_chi_squared_with_slightly_varied_distribution(): void
    {
        $observed = [
            1 => 9,
            2 => 11,
            3 => 10,
            4 => 10,
            5 => 9,
            6 => 11,
        ];

        $expected = $this->statistics->expectedDistribution(6, 60);

        $chiSquared = $this->statistics->chiSquaredTest($observed, $expected);

        // Chi² = ((9-10)²/10 + (11-10)²/10 + (10-10)²/10 + (10-10)²/10 + (9-10)²/10 + (11-10)²/10)
        // Chi² = (1/10 + 1/10 + 0 + 0 + 1/10 + 1/10) = 0.4
        $this->assertEqualsWithDelta(0.4, $chiSquared, 0.0001);
    }

    public function test_chi_squared_with_heavily_biased_distribution(): void
    {
        // Heavily biased towards 6
        $observed = [
            1 => 2,
            2 => 3,
            3 => 4,
            4 => 5,
            5 => 6,
            6 => 40,
        ];

        $expected = $this->statistics->expectedDistribution(6, 60);

        $chiSquared = $this->statistics->chiSquaredTest($observed, $expected);

        // Should have high chi-squared value
        $this->assertGreaterThan(50.0, $chiSquared);
    }

    public function test_chi_squared_with_missing_observed_values(): void
    {
        // Some sides never rolled
        $observed = [
            1 => 15,
            2 => 15,
            3 => 15,
            4 => 15,
            // 5 and 6 not rolled (implicitly 0)
        ];

        $expected = $this->statistics->expectedDistribution(6, 60);

        $chiSquared = $this->statistics->chiSquaredTest($observed, $expected);

        // Chi² includes contribution from missing values
        // Each missing side: (0-10)²/10 = 100/10 = 10
        // Each present side: (15-10)²/10 = 25/10 = 2.5
        // Total: 4*2.5 + 2*10 = 10 + 20 = 30
        $this->assertEqualsWithDelta(30.0, $chiSquared, 0.0001);
    }

    public function test_analyze_throws_exception_for_empty_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot analyze empty array');

        $this->statistics->analyze([]);
    }

    public function test_analyze_with_single_value(): void
    {
        $values = [5];

        $result = $this->statistics->analyze($values);

        $this->assertSame(5.0, $result->mean);
        $this->assertSame(5.0, $result->median);
        $this->assertSame(5, $result->mode);
        $this->assertSame(0.0, $result->standardDeviation);
        $this->assertSame(5, $result->min);
        $this->assertSame(5, $result->max);
        $this->assertSame(0, $result->range);
        $this->assertSame([5 => 1], $result->distribution);
    }

    public function test_real_world_dice_rolls(): void
    {
        // Simulate 10 d6 rolls
        $values = [3, 5, 6, 2, 6, 4, 1, 6, 5, 2];

        $result = $this->statistics->analyze($values);

        $this->assertSame(4.0, $result->mean);
        $this->assertSame(4.5, $result->median); // (4 + 5) / 2
        $this->assertSame(6, $result->mode); // 6 appears 3 times
        $this->assertSame(1, $result->min);
        $this->assertSame(6, $result->max);
        $this->assertSame(5, $result->range);
        $this->assertEqualsWithDelta(1.8257, $result->standardDeviation, 0.0001);
    }
}
