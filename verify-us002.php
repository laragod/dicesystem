<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Laragod\DiceSystem\Core\Random\Mt19937Engine;

echo "=== US-002 Verification Script ===\n\n";

// Test 1: Same seed produces identical sequences
echo "Test 1: Same seed produces identical sequences\n";
$engine1 = new Mt19937Engine(42);
$engine2 = new Mt19937Engine(42);

$seq1 = [];
$seq2 = [];
for ($i = 0; $i < 10; $i++) {
    $seq1[] = $engine1->next(1, 100);
    $seq2[] = $engine2->next(1, 100);
}

echo "Engine 1: " . implode(', ', $seq1) . "\n";
echo "Engine 2: " . implode(', ', $seq2) . "\n";
echo "Match: " . ($seq1 === $seq2 ? "PASS" : "FAIL") . "\n\n";

// Test 2: Different seeds produce different sequences
echo "Test 2: Different seeds produce different sequences\n";
$engine3 = new Mt19937Engine(1337);
$seq3 = [];
for ($i = 0; $i < 10; $i++) {
    $seq3[] = $engine3->next(1, 100);
}
echo "Engine 3 (seed 1337): " . implode(', ', $seq3) . "\n";
echo "Different from Engine 1: " . ($seq1 !== $seq3 ? "PASS" : "FAIL") . "\n\n";

// Test 3: getSeed returns current seed
echo "Test 3: getSeed returns current seed\n";
$engine4 = new Mt19937Engine(999);
echo "Set seed: 999\n";
echo "Got seed: " . $engine4->getSeed() . "\n";
echo "Match: " . ($engine4->getSeed() === 999 ? "PASS" : "FAIL") . "\n\n";

// Test 4: Reset restores to original state
echo "Test 4: Reset restores to original state\n";
$engine5 = new Mt19937Engine(777);
$original = [];
for ($i = 0; $i < 5; $i++) {
    $original[] = $engine5->next(1, 20);
}
echo "Original sequence: " . implode(', ', $original) . "\n";

$engine5->reset();
$afterReset = [];
for ($i = 0; $i < 5; $i++) {
    $afterReset[] = $engine5->next(1, 20);
}
echo "After reset: " . implode(', ', $afterReset) . "\n";
echo "Match: " . ($original === $afterReset ? "PASS" : "FAIL") . "\n\n";

// Test 5: Seed immutability
echo "Test 5: Seed is immutable\n";
$engine6 = new Mt19937Engine(100);
try {
    $engine6->setSeed(200);
    echo "FAIL: Should have thrown exception\n";
} catch (LogicException $e) {
    echo "PASS: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: getSeed returns null when not set
echo "Test 6: getSeed returns null when not set\n";
$engine7 = new Mt19937Engine();
echo "Seed: " . ($engine7->getSeed() === null ? "null" : $engine7->getSeed()) . "\n";
echo "Match: " . ($engine7->getSeed() === null ? "PASS" : "FAIL") . "\n\n";

echo "=== All verification tests complete ===\n";
