<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Tests\Unit\Core\Dice;

use DateTimeImmutable;
use Laragod\DiceSystem\Core\Dice\CustomDie;
use Laragod\DiceSystem\Core\Dice\CustomDieResult;
use Laragod\DiceSystem\Core\Exception\InvalidDieException;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;
use PHPUnit\Framework\TestCase;

class CustomDieTest extends TestCase
{
    public function testStringFaces(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $faces = ['red', 'blue', 'green', 'yellow'];
        $die = new CustomDie($faces, $engine);

        $result = $die->roll();
        $this->assertInstanceOf(CustomDieResult::class, $result);
        $this->assertContains($result->value, $faces);
        $this->assertSame(4, $result->faceCount);
    }

    public function testIntegerFacesWithCustomValues(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $faces = [0, 5, 10, 15, 20];
        $die = new CustomDie($faces, $engine);

        $result = $die->roll();
        $this->assertInstanceOf(CustomDieResult::class, $result);
        $this->assertContains($result->value, $faces);
        $this->assertSame(5, $result->faceCount);
    }

    public function testMixedContentFaces(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $faces = ['attack', 'defend', 'special', 'blank'];
        $die = new CustomDie($faces, $engine);

        $result = $die->roll();
        $this->assertInstanceOf(CustomDieResult::class, $result);
        $this->assertContains($result->value, $faces);
        $this->assertSame(4, $result->faceCount);
    }

    public function testDeterministicBehaviorWithSeeds(): void
    {
        $faces = ['red', 'blue', 'green', 'yellow'];

        // First sequence
        $engine1 = new Mt19937Engine();
        $engine1->setSeed(42);
        $die1 = new CustomDie($faces, $engine1);

        $results1 = [];
        for ($i = 0; $i < 10; $i++) {
            $results1[] = $die1->roll()->value;
        }

        // Second sequence with same seed
        $engine2 = new Mt19937Engine();
        $engine2->setSeed(42);
        $die2 = new CustomDie($faces, $engine2);

        $results2 = [];
        for ($i = 0; $i < 10; $i++) {
            $results2[] = $die2->roll()->value;
        }

        $this->assertSame($results1, $results2, 'Same seed should produce identical sequences');
    }

    public function testDifferentSeedsProduceDifferentSequences(): void
    {
        $faces = ['red', 'blue', 'green', 'yellow'];

        // First sequence with seed 42
        $engine1 = new Mt19937Engine();
        $engine1->setSeed(42);
        $die1 = new CustomDie($faces, $engine1);

        $results1 = [];
        for ($i = 0; $i < 10; $i++) {
            $results1[] = $die1->roll()->value;
        }

        // Second sequence with seed 99
        $engine2 = new Mt19937Engine();
        $engine2->setSeed(99);
        $die2 = new CustomDie($faces, $engine2);

        $results2 = [];
        for ($i = 0; $i < 10; $i++) {
            $results2[] = $die2->roll()->value;
        }

        $this->assertNotSame($results1, $results2, 'Different seeds should produce different sequences');
    }

    public function testEmptyFacesArrayThrowsException(): void
    {
        $this->expectException(InvalidDieException::class);
        $this->expectExceptionMessage('CustomDie must have at least one face');

        $engine = new Mt19937Engine();
        new CustomDie([], $engine);
    }

    public function testFacesImmutability(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $faces = ['red', 'blue', 'green', 'yellow'];
        $die = new CustomDie($faces, $engine);

        // Get faces and modify the returned array
        $returnedFaces = $die->getFaces();
        $returnedFaces[0] = 'modified';
        $returnedFaces[] = 'extra';

        // Original die should not be affected
        $actualFaces = $die->getFaces();
        $this->assertSame(['red', 'blue', 'green', 'yellow'], $actualFaces);
        $this->assertCount(4, $actualFaces);
    }

    public function testResultIncludesCorrectMetadata(): void
    {
        $engine = new Mt19937Engine();
        $seed = 54321;
        $engine->setSeed($seed);

        $faces = ['attack', 'defend', 'special', 'blank'];
        $die = new CustomDie($faces, $engine);

        $beforeRoll = new DateTimeImmutable();
        $result = $die->roll();
        $afterRoll = new DateTimeImmutable();

        // Check all properties are set
        $this->assertContains($result->value, $faces);
        $this->assertSame(4, $result->faceCount);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->rolledAt);
        $this->assertSame($seed, $result->seed);

        // Timestamp should be between before and after
        $this->assertGreaterThanOrEqual(
            $beforeRoll->getTimestamp(),
            $result->rolledAt->getTimestamp()
        );
        $this->assertLessThanOrEqual(
            $afterRoll->getTimestamp(),
            $result->rolledAt->getTimestamp()
        );
    }

    public function testResultIsReadonly(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(999);

        $faces = ['red', 'blue', 'green'];
        $die = new CustomDie($faces, $engine);
        $result = $die->roll();

        // Properties are readonly - this should be enforced by PHP's type system
        $this->assertIsString($result->value);
        $this->assertIsInt($result->faceCount);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->rolledAt);
        $this->assertIsInt($result->seed);
    }

    public function testSingleFaceAlwaysReturnsSameValue(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $faces = ['only'];
        $die = new CustomDie($faces, $engine);

        for ($i = 0; $i < 10; $i++) {
            $result = $die->roll();
            $this->assertSame('only', $result->value);
            $this->assertSame(1, $result->faceCount);
        }
    }

    public function testGetFacesReturnsCorrectArray(): void
    {
        $engine = new Mt19937Engine();

        $faces = ['a', 'b', 'c', 'd', 'e'];
        $die = new CustomDie($faces, $engine);

        $returnedFaces = $die->getFaces();
        $this->assertSame($faces, $returnedFaces);
        $this->assertCount(5, $returnedFaces);
    }

    public function testEngineNotSeededReturnsNullSeed(): void
    {
        $engine = new Mt19937Engine();
        // Don't set seed

        $faces = ['red', 'blue', 'green'];
        $die = new CustomDie($faces, $engine);
        $result = $die->roll();

        $this->assertNull($result->seed);
    }

    public function testMultipleRollsProduceValidResults(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(99999);

        $faces = ['alpha', 'beta', 'gamma', 'delta'];
        $die = new CustomDie($faces, $engine);

        // Roll multiple times to verify all results are valid
        for ($i = 0; $i < 100; $i++) {
            $result = $die->roll();
            $this->assertContains($result->value, $faces, "Roll {$i}: invalid face value");
            $this->assertSame(4, $result->faceCount, "Roll {$i}: incorrect face count");
        }
    }

    public function testFacesWithAssociativeArrayAreReindexed(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        // Associative array should be reindexed
        $faces = [10 => 'ten', 20 => 'twenty', 30 => 'thirty'];
        $die = new CustomDie($faces, $engine);

        $returnedFaces = $die->getFaces();
        $this->assertSame(['ten', 'twenty', 'thirty'], $returnedFaces);
        $this->assertSame([0, 1, 2], array_keys($returnedFaces));
    }

    public function testNumericFacesWithZero(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $faces = [0, 0, 1, 1, 2, 3];
        $die = new CustomDie($faces, $engine);

        $result = $die->roll();
        $this->assertContains($result->value, [0, 1, 2, 3]);
        $this->assertSame(6, $result->faceCount);
    }
}
