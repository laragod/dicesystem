<?php

declare(strict_types=1);

namespace Tests\Unit\Core\History;

use DateTimeImmutable;
use Laragod\DiceSystem\Core\Dice\DieResult;
use Laragod\DiceSystem\Core\Dice\RollResult;
use Laragod\DiceSystem\Core\History\RollHistory;
use PHPUnit\Framework\TestCase;

class RollHistoryTest extends TestCase
{
    private function createMockRollResult(
        string $notation = '2d6',
        int $total = 7,
        ?DateTimeImmutable $rolledAt = null,
        ?int $seed = null
    ): RollResult {
        $rolledAt ??= new DateTimeImmutable();

        // Create mock DieResult objects
        $die1 = new DieResult(
            value: 3,
            sides: 6,
            rolledAt: $rolledAt,
            seed: $seed
        );
        $die2 = new DieResult(
            value: 4,
            sides: 6,
            rolledAt: $rolledAt,
            seed: $seed
        );

        return new RollResult(
            notation: $notation,
            individualResults: [$die1, $die2],
            total: $total,
            rolledAt: $rolledAt,
            seed: $seed
        );
    }

    public function test_add_roll_to_history(): void
    {
        $history = new RollHistory();
        $result = $this->createMockRollResult();

        $history->add($result);

        $this->assertCount(1, $history->getAll());
        $this->assertSame($result, $history->getAll()[0]);
    }

    public function test_add_multiple_rolls_to_history(): void
    {
        $history = new RollHistory();
        $result1 = $this->createMockRollResult('2d6', 7);
        $result2 = $this->createMockRollResult('1d20', 15);
        $result3 = $this->createMockRollResult('3d8', 12);

        $history->add($result1);
        $history->add($result2);
        $history->add($result3);

        $this->assertCount(3, $history->getAll());
    }

    public function test_get_all_returns_all_rolls(): void
    {
        $history = new RollHistory();
        $result1 = $this->createMockRollResult('2d6', 7);
        $result2 = $this->createMockRollResult('1d20', 15);

        $history->add($result1);
        $history->add($result2);

        $allRolls = $history->getAll();

        $this->assertCount(2, $allRolls);
        $this->assertSame($result1, $allRolls[0]);
        $this->assertSame($result2, $allRolls[1]);
    }

    public function test_get_recent_returns_correct_number(): void
    {
        $history = new RollHistory();

        for ($i = 0; $i < 10; $i++) {
            $history->add($this->createMockRollResult('2d6', $i));
        }

        $recent = $history->getRecent(3);

        $this->assertCount(3, $recent);
        // Should return the last 3 added (totals: 7, 8, 9)
        $this->assertSame(7, $recent[0]->total);
        $this->assertSame(8, $recent[1]->total);
        $this->assertSame(9, $recent[2]->total);
    }

    public function test_get_recent_with_limit_greater_than_count(): void
    {
        $history = new RollHistory();
        $result1 = $this->createMockRollResult('2d6', 7);
        $result2 = $this->createMockRollResult('1d20', 15);

        $history->add($result1);
        $history->add($result2);

        $recent = $history->getRecent(10);

        $this->assertCount(2, $recent);
    }

    public function test_filter_by_notation(): void
    {
        $history = new RollHistory();
        $result1 = $this->createMockRollResult('2d6', 7);
        $result2 = $this->createMockRollResult('1d20', 15);
        $result3 = $this->createMockRollResult('2d6', 9);

        $history->add($result1);
        $history->add($result2);
        $history->add($result3);

        $filtered = $history->filter(fn(RollResult $r) => $r->notation === '2d6');

        $this->assertCount(2, $filtered);
    }

    public function test_filter_by_total_value(): void
    {
        $history = new RollHistory();
        $result1 = $this->createMockRollResult('2d6', 5);
        $result2 = $this->createMockRollResult('2d6', 10);
        $result3 = $this->createMockRollResult('2d6', 12);
        $result4 = $this->createMockRollResult('2d6', 8);

        $history->add($result1);
        $history->add($result2);
        $history->add($result3);
        $history->add($result4);

        $filtered = $history->filter(fn(RollResult $r) => $r->total >= 10);

        $this->assertCount(2, $filtered);
    }

    public function test_filter_by_date_range(): void
    {
        $history = new RollHistory();

        $date1 = new DateTimeImmutable('2024-01-01 10:00:00');
        $date2 = new DateTimeImmutable('2024-01-02 10:00:00');
        $date3 = new DateTimeImmutable('2024-01-03 10:00:00');

        $result1 = $this->createMockRollResult('2d6', 7, $date1);
        $result2 = $this->createMockRollResult('2d6', 8, $date2);
        $result3 = $this->createMockRollResult('2d6', 9, $date3);

        $history->add($result1);
        $history->add($result2);
        $history->add($result3);

        $cutoffDate = new DateTimeImmutable('2024-01-02 00:00:00');
        $filtered = $history->filter(fn(RollResult $r) => $r->rolledAt >= $cutoffDate);

        $this->assertCount(2, $filtered);
    }

    public function test_filter_by_seed(): void
    {
        $history = new RollHistory();
        $result1 = $this->createMockRollResult('2d6', 7, null, 12345);
        $result2 = $this->createMockRollResult('2d6', 8, null, 67890);
        $result3 = $this->createMockRollResult('2d6', 9, null, 12345);

        $history->add($result1);
        $history->add($result2);
        $history->add($result3);

        $filtered = $history->filter(fn(RollResult $r) => $r->seed === 12345);

        $this->assertCount(2, $filtered);
    }

    public function test_max_size_enforcement_removes_oldest(): void
    {
        $history = new RollHistory(maxSize: 5);

        for ($i = 0; $i < 10; $i++) {
            $history->add($this->createMockRollResult('2d6', $i));
        }

        $this->assertCount(5, $history->getAll());

        // Should only have the last 5 entries (totals: 5, 6, 7, 8, 9)
        $allRolls = $history->getAll();
        $this->assertSame(5, $allRolls[0]->total);
        $this->assertSame(9, $allRolls[4]->total);
    }

    public function test_max_size_with_exact_limit(): void
    {
        $history = new RollHistory(maxSize: 3);

        $history->add($this->createMockRollResult('2d6', 1));
        $history->add($this->createMockRollResult('2d6', 2));
        $history->add($this->createMockRollResult('2d6', 3));

        $this->assertCount(3, $history->getAll());

        // Add one more to trigger eviction
        $history->add($this->createMockRollResult('2d6', 4));

        $this->assertCount(3, $history->getAll());

        // First entry (total: 1) should be removed
        $allRolls = $history->getAll();
        $this->assertSame(2, $allRolls[0]->total);
        $this->assertSame(4, $allRolls[2]->total);
    }

    public function test_clear_empties_history(): void
    {
        $history = new RollHistory();
        $history->add($this->createMockRollResult('2d6', 7));
        $history->add($this->createMockRollResult('1d20', 15));

        $this->assertCount(2, $history->getAll());

        $history->clear();

        $this->assertCount(0, $history->getAll());
        $this->assertEmpty($history->getAll());
    }

    public function test_count_returns_accurate_count(): void
    {
        $history = new RollHistory();

        $this->assertSame(0, $history->count());

        $history->add($this->createMockRollResult('2d6', 7));
        $this->assertSame(1, $history->count());

        $history->add($this->createMockRollResult('1d20', 15));
        $this->assertSame(2, $history->count());

        $history->clear();
        $this->assertSame(0, $history->count());
    }

    public function test_count_respects_max_size(): void
    {
        $history = new RollHistory(maxSize: 3);

        for ($i = 0; $i < 5; $i++) {
            $history->add($this->createMockRollResult('2d6', $i));
        }

        $this->assertSame(3, $history->count());
    }

    public function test_empty_history_operations(): void
    {
        $history = new RollHistory();

        $this->assertCount(0, $history->getAll());
        $this->assertCount(0, $history->getRecent(5));
        $this->assertCount(0, $history->filter(fn($r) => true));
        $this->assertSame(0, $history->count());
    }

    public function test_filter_returns_empty_array_when_no_matches(): void
    {
        $history = new RollHistory();
        $history->add($this->createMockRollResult('2d6', 7));
        $history->add($this->createMockRollResult('2d6', 8));

        $filtered = $history->filter(fn(RollResult $r) => $r->total > 100);

        $this->assertCount(0, $filtered);
        $this->assertIsArray($filtered);
    }

    public function test_complex_filter_combination(): void
    {
        $history = new RollHistory();

        $date1 = new DateTimeImmutable('2024-01-01 10:00:00');
        $date2 = new DateTimeImmutable('2024-01-02 10:00:00');

        $history->add($this->createMockRollResult('2d6', 10, $date1));
        $history->add($this->createMockRollResult('1d20', 15, $date1));
        $history->add($this->createMockRollResult('2d6', 12, $date2));
        $history->add($this->createMockRollResult('1d20', 8, $date2));

        // Filter for 2d6 rolls with total >= 10
        $filtered = $history->filter(
            fn(RollResult $r) => $r->notation === '2d6' && $r->total >= 10
        );

        $this->assertCount(2, $filtered);
    }
}
