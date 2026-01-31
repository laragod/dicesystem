<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Laragod\DiceSystem\Core\Parser\DiceNotationParser;
use Laragod\DiceSystem\Core\Parser\DiceExpression;
use Laragod\DiceSystem\Core\Exception\InvalidNotationException;
use Laragod\DiceSystem\Core\Exception\InvalidDieException;

echo "=== US-004: Dice Notation Parser Verification ===\n\n";

$parser = new DiceNotationParser();
$passed = 0;
$failed = 0;

// Test valid notations
$validTests = [
    ['2d6', 2, 6],
    ['1d20', 1, 20],
    ['3d8', 3, 8],
    ['100d100', 100, 100],
    ['1d2', 1, 2],
    ['2D6', 2, 6], // case insensitive
    ['3D20', 3, 20],
];

echo "Testing Valid Notations:\n";
foreach ($validTests as [$notation, $expectedQuantity, $expectedSides]) {
    try {
        $expr = $parser->parse($notation);
        if ($expr->quantity === $expectedQuantity && $expr->sides === $expectedSides) {
            echo "  ✓ '{$notation}' -> {$expr->quantity}d{$expr->sides}\n";
            $passed++;
        } else {
            echo "  ✗ '{$notation}' -> Got {$expr->quantity}d{$expr->sides}, expected {$expectedQuantity}d{$expectedSides}\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "  ✗ '{$notation}' threw exception: {$e->getMessage()}\n";
        $failed++;
    }
}

// Test __toString()
echo "\nTesting __toString():\n";
try {
    $expr = $parser->parse('2d6');
    $str = (string) $expr;
    if ($str === '2d6') {
        echo "  ✓ __toString() returns '2d6'\n";
        $passed++;
    } else {
        echo "  ✗ __toString() returned '{$str}', expected '2d6'\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "  ✗ __toString() test threw exception: {$e->getMessage()}\n";
    $failed++;
}

// Test case normalization
try {
    $expr = $parser->parse('3D20');
    $str = (string) $expr;
    if ($str === '3d20') {
        echo "  ✓ __toString() normalizes case: '3D20' -> '3d20'\n";
        $passed++;
    } else {
        echo "  ✗ __toString() didn't normalize: got '{$str}'\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "  ✗ Case normalization test threw exception: {$e->getMessage()}\n";
    $failed++;
}

// Test invalid notations
$invalidTests = [
    'd6',
    '2x6',
    'abc',
    '',
    '2d',
    'd',
    '2d0',
    '1d1',
    '2 d 6',
];

echo "\nTesting Invalid Notations:\n";
foreach ($invalidTests as $notation) {
    try {
        $expr = $parser->parse($notation);
        echo "  ✗ '{$notation}' should have thrown InvalidNotationException but didn't\n";
        $failed++;
    } catch (InvalidNotationException | InvalidDieException $e) {
        echo "  ✓ '{$notation}' correctly threw exception: " . get_class($e) . "\n";
        $passed++;
    } catch (Exception $e) {
        echo "  ✗ '{$notation}' threw unexpected exception: " . get_class($e) . "\n";
        $failed++;
    }
}

// Test DiceExpression validation
echo "\nTesting DiceExpression Validation:\n";

// Test quantity < 1
try {
    new DiceExpression(0, 6);
    echo "  ✗ DiceExpression(0, 6) should have thrown InvalidDieException\n";
    $failed++;
} catch (InvalidDieException $e) {
    echo "  ✓ DiceExpression(0, 6) correctly threw InvalidDieException\n";
    $passed++;
}

// Test sides < 2
try {
    new DiceExpression(2, 1);
    echo "  ✗ DiceExpression(2, 1) should have thrown InvalidDieException\n";
    $failed++;
} catch (InvalidDieException $e) {
    echo "  ✓ DiceExpression(2, 1) correctly threw InvalidDieException\n";
    $passed++;
}

// Test valid minimum values
try {
    $expr = new DiceExpression(1, 2);
    if ($expr->quantity === 1 && $expr->sides === 2) {
        echo "  ✓ DiceExpression(1, 2) accepts valid minimum values\n";
        $passed++;
    } else {
        echo "  ✗ DiceExpression(1, 2) has wrong values\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "  ✗ DiceExpression(1, 2) threw unexpected exception: {$e->getMessage()}\n";
    $failed++;
}

// Test readonly
echo "\nTesting Readonly Properties:\n";
try {
    $expr = new DiceExpression(2, 6);
    $reflection = new ReflectionClass($expr);
    if ($reflection->isReadOnly()) {
        echo "  ✓ DiceExpression class is readonly\n";
        $passed++;
    } else {
        echo "  ✗ DiceExpression class is not readonly\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "  ✗ Readonly test threw exception: {$e->getMessage()}\n";
    $failed++;
}

// Summary
echo "\n=== Summary ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed.\n";
    exit(1);
}
