<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Roller;

use RuntimeException;
use Laragod\DiceSystem\Contract\DiceRollerInterface;
use Laragod\DiceSystem\Contract\HasRemainingInterface;
use Laragod\DiceSystem\Contract\RandomSourceInterface;
use Laragod\DiceSystem\Contract\ResettableInterface;
use Laragod\DiceSystem\Support\Base2d6;

class DeckDiceRoller implements DiceRollerInterface, ResettableInterface, HasRemainingInterface
{
    /** @var int[] */
    protected array $deck = [];

    public function __construct(
        protected RandomSourceInterface $rng,
        protected int $multiplier = 1, // 36 * multiplier cards
    ) {
        if ($this->multiplier < 1) {
            throw new RuntimeException('Multiplier must be >= 1');
        }
        $this->reset();
    }

    public function roll(): int
    {
        if ($this->deck === []) {
            $this->reset();
        }

        /** @var int $v */
        $v = array_pop($this->deck);
        return $v;
    }

    public function remaining(): int
    {
        return count($this->deck);
    }

    public function reset(): void
    {
        $this->deck = $this->buildDeck($this->multiplier);
        $this->shuffleInPlace($this->deck);
    }

    /** @return int[] */
    protected function buildDeck(int $multiplier): array
    {
        $weights = Base2d6::weights();
        $deck = [];

        foreach ($weights as $sum => $copies) {
            $total = $copies * $multiplier;
            for ($i = 0; $i < $total; $i++) {
                $deck[] = $sum;
            }
        }

        $expected = 36 * $multiplier;
        if (count($deck) !== $expected) {
            throw new RuntimeException("Deck size mismatch: expected {$expected}");
        }

        return $deck;
    }

    /** @param int[] $arr */
    protected function shuffleInPlace(array &$arr): void
    {
        for ($i = count($arr) - 1; $i > 0; $i--) {
            $j = $this->rng->int(0, $i);
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }
}
