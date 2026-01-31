<?php
/**
 * Example 05: Statistical Analysis of Dice Rolls
 *
 * Demonstrates how to use the statistics module to analyze roll distributions,
 * calculate statistical measures, and test for fairness.
 * Run with: php examples/05-statistics.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Statistics\RollStatistics;
use Laragod\DiceSystem\Core\History\RollHistory;

echo "=== PHP Dice System: Statistics ===\n\n";

// Example 1: Basic statistical analysis
echo "Example 1: Basic statistical analysis\n";
echo "------------------------------------\n";

$stats = new RollStatistics();

$rolls = [2, 3, 4, 3, 5, 6, 3, 4, 2, 5];
$result = $stats->analyze($rolls);

echo "Rolls: " . implode(', ', $rolls) . "\n";
echo "Count: " . count($rolls) . "\n";
echo "Mean: " . number_format($result->mean, 2) . "\n";
echo "Median: " . number_format($result->median, 2) . "\n";
echo "Mode: " . $result->mode . "\n";
echo "Std Dev: " . number_format($result->standardDeviation, 2) . "\n";
echo "Min: " . $result->min . "\n";
echo "Max: " . $result->max . "\n";
echo "Range: " . $result->range . "\n\n";

// Example 2: Distribution analysis
echo "Example 2: Distribution of 1d6 rolls\n";
echo "-----------------------------------\n";

$roller = new DiceRoller();
$rolls = [];

// Roll 600 times (100 per face expected for fair die)
for ($i = 0; $i < 600; $i++) {
    $result = $roller->roll('1d6');
    $rolls[] = $result->total;
}

$analysis = $stats->analyze($rolls);

echo "1000 rolls of 1d6:\n";
echo "Mean: " . number_format($analysis->mean, 2) . "\n";
echo "Expected: 3.50\n";
echo "Median: " . number_format($analysis->median, 2) . "\n\n";

echo "Distribution:\n";
foreach ($analysis->distribution as $value => $frequency) {
    $percentage = ($frequency / 600) * 100;
    $bar = str_repeat('=', (int)($frequency / 10));
    printf("  %d: %3d (%.1f%%) %s\n", $value, $frequency, $percentage, $bar);
}
echo "\n";

// Example 3: Different dice types
echo "Example 3: Statistical comparison of different dice\n";
echo "---------------------------------------------------\n";

$diceTypes = ['1d4', '1d6', '1d8', '1d10', '1d12', '1d20'];

foreach ($diceTypes as $notation) {
    $rolls = [];
    for ($i = 0; $i < 1000; $i++) {
        $result = $roller->roll($notation);
        $rolls[] = $result->total;
    }

    $analysis = $stats->analyze($rolls);
    printf(
        "%5s: Mean=%.2f, Median=%.2f, StdDev=%.2f, Range=%d\n",
        $notation,
        $analysis->mean,
        $analysis->median,
        $analysis->standardDeviation,
        $analysis->range
    );
}
echo "\n";

// Example 4: Distribution comparison
echo "Example 4: Expected vs Actual distribution\n";
echo "---------------------------------------\n";

$rolls = [];
for ($i = 0; $i < 1000; $i++) {
    $result = $roller->roll('1d6');
    $rolls[] = $result->total;
}

$analysis = $stats->analyze($rolls);
$expected = $stats->expectedDistribution(sides: 6, count: 1000);

echo "1d6 - Observed vs Expected (1000 rolls):\n";
foreach ($expected as $value => $expectedFreq) {
    $observed = $analysis->distribution[$value] ?? 0;
    $diff = $observed - $expectedFreq;
    $diffStr = ($diff > 0 ? '+' : '') . $diff;
    printf("  %d: Observed=%3d, Expected=%.0f, Diff=%s\n", $value, $observed, $expectedFreq, $diffStr);
}
echo "\n";

// Example 5: Chi-squared test for fairness
echo "Example 5: Chi-squared test (fairness test)\n";
echo "----------------------------------------\n";

$rolls = [];
for ($i = 0; $i < 1000; $i++) {
    $result = $roller->roll('1d20');
    $rolls[] = $result->total;
}

$analysis = $stats->analyze($rolls);
$expected = $stats->expectedDistribution(sides: 20, count: 1000);

$chiSquared = $stats->chiSquaredTest($analysis->distribution, $expected);

echo "1d20 fairness test (1000 rolls):\n";
echo "Chi-squared statistic: " . number_format($chiSquared, 4) . "\n";
echo "Critical value (p=0.05): 30.58\n";

if ($chiSquared < 30.58) {
    echo "Result: Die appears FAIR\n";
} else {
    echo "Result: Die may be BIASED\n";
}
echo "\n";

// Example 6: Multiple dice comparison
echo "Example 6: 2d6 vs 1d12 comparison\n";
echo "-------------------------------\n";

$rolls2d6 = [];
$rolls1d12 = [];

for ($i = 0; $i < 1000; $i++) {
    $rolls2d6[] = $roller->roll('2d6')->total;
    $rolls1d12[] = $roller->roll('1d12')->total;
}

$analysis2d6 = $stats->analyze($rolls2d6);
$analysis1d12 = $stats->analyze($rolls1d12);

echo "2d6 (0 rolls, average ~7):\n";
printf("  Mean: %.2f, Median: %.2f, StdDev: %.2f\n", $analysis2d6->mean, $analysis2d6->median, $analysis2d6->standardDeviation);

echo "\n1d12 (1000 rolls, average ~6.5):\n";
printf("  Mean: %.2f, Median: %.2f, StdDev: %.2f\n", $analysis1d12->mean, $analysis1d12->median, $analysis1d12->standardDeviation);

echo "\nObservation: 2d6 has tighter distribution (clustered around mean)\n";
echo "              1d12 has wider distribution (more uniform)\n\n";

// Example 7: Roll history statistics
echo "Example 7: Statistics from roll history\n";
echo "------------------------------------\n";

$history = new RollHistory();

// Perform some rolls
for ($i = 0; $i < 100; $i++) {
    $history->add($roller->roll('2d6'));
}

// Get all rolls as array of totals
$totals = array_map(fn($r) => $r->total, $history->getAll());
$histStats = $stats->analyze($totals);

echo "100 rolls of 2d6 from history:\n";
printf("  Mean: %.2f\n", $histStats->mean);
printf("  Median: %.2f\n", $histStats->median);
printf("  Mode: %d\n", $histStats->mode);
printf("  Min: %d, Max: %d\n", $histStats->min, $histStats->max);
echo "\n";

// Example 8: Full statistical report
echo "Example 8: Full statistical report\n";
echo "---------------------------------\n";

$rolls = [];
for ($i = 0; $i < 5000; $i++) {
    $result = $roller->roll('3d6');
    $rolls[] = $result->total;
}

$analysis = $stats->analyze($rolls);

echo "3d6 Full Report (5000 rolls):\n";
echo "========================================\n";
printf("Sample Size:       %d\n", count($rolls));
printf("Mean:              %.4f\n", $analysis->mean);
printf("Median:            %.4f\n", $analysis->median);
printf("Mode:              %s\n", $analysis->mode);
printf("Standard Deviation: %.4f\n", $analysis->standardDeviation);
printf("Minimum:           %s\n", $analysis->min);
printf("Maximum:           %s\n", $analysis->max);
printf("Range:             %s\n", $analysis->range);
echo "\nDistribution:\n";
foreach ($analysis->distribution as $value => $frequency) {
    $percentage = ($frequency / 5000) * 100;
    printf("  Sum %2d: %4d rolls (%.2f%%)\n", $value, $frequency, $percentage);
}
echo "\n";

// Example 9: Comparing fairness across multiple runs
echo "Example 9: Statistical consistency test\n";
echo "------------------------------------\n";

for ($run = 1; $run <= 3; $run++) {
    $rolls = [];
    for ($i = 0; $i < 600; $i++) {
        $result = $roller->roll('1d6');
        $rolls[] = $result->total;
    }

    $analysis = $stats->analyze($rolls);
    $expected = $stats->expectedDistribution(sides: 6, count: 600);
    $chiSquared = $stats->chiSquaredTest($analysis->distribution, $expected);

    echo "Run $run: Chi-squared = " . number_format($chiSquared, 4);
    echo " -> " . ($chiSquared < 11.07 ? "FAIR" : "POSSIBLY BIASED");
    echo "\n";
}

echo "\n=== Complete! ===\n";
