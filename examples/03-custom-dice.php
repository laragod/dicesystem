<?php
/**
 * Example 03: Custom Dice with Non-Numeric Faces
 *
 * Demonstrates how to create and roll dice with custom face values
 * like strings, cards, or other objects.
 * Run with: php examples/03-custom-dice.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\CustomDie;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

echo "=== PHP Dice System: Custom Dice ===\n\n";

// Example 1: Simple coin flip
echo "Example 1: Coin flip\n";
echo "-------------------\n";

$coin = new CustomDie(['Heads', 'Tails'], new SystemRandomEngine());

for ($i = 0; $i < 5; $i++) {
    $result = $coin->roll();
    echo "Flip " . ($i + 1) . ": " . $result->value . "\n";
}
echo "\n";

// Example 2: Element selector
echo "Example 2: Element selector\n";
echo "---------------------------\n";

$elements = new CustomDie(
    ['Fire', 'Water', 'Earth', 'Air', 'Lightning'],
    new SystemRandomEngine()
);

echo "Random elements:\n";
for ($i = 0; $i < 10; $i++) {
    $result = $elements->roll();
    echo "  " . $result->value . "\n";
}
echo "\n";

// Example 3: Card deck
echo "Example 3: Card deck\n";
echo "-------------------\n";

$suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
$ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

$cards = [];
foreach ($suits as $suit) {
    foreach ($ranks as $rank) {
        $cards[] = "$rank of $suit";
    }
}

$cardDeck = new CustomDie($cards, new SystemRandomEngine());

echo "Drawing 5 cards:\n";
for ($i = 0; $i < 5; $i++) {
    $result = $cardDeck->roll();
    echo "  " . $result->value . "\n";
}
echo "\n";

// Example 4: Reproducible custom dice with seed
echo "Example 4: Reproducible draws\n";
echo "------------------------------\n";

// First draw
$engine1 = new Mt19937Engine();
$engine1->setSeed(777);
$colors1 = new CustomDie(['Red', 'Green', 'Blue', 'Yellow'], $engine1);

$draw1 = $colors1->roll();
echo "First draw: " . $draw1->value . " (seed: " . $draw1->seed . ")\n";

// Reset and draw again
$engine1->reset();
$draw2 = $colors1->roll();
echo "After reset: " . $draw2->value . " (same? " . ($draw1->value === $draw2->value ? "YES" : "NO") . ")\n\n";

// Example 5: Custom dice with numeric and semantic value
echo "Example 5: Rarity die\n";
echo "---------------------\n";

class ItemRarity {
    public function __construct(
        public string $name,
        public int $value,
        public string $color
    ) {}

    public function __toString(): string {
        return "$this->name (Value: $this->value)";
    }
}

$rarities = new CustomDie([
    new ItemRarity('Common', 1, 'Gray'),
    new ItemRarity('Uncommon', 5, 'Green'),
    new ItemRarity('Rare', 25, 'Blue'),
    new ItemRarity('Epic', 100, 'Purple'),
    new ItemRarity('Legendary', 500, 'Gold'),
], new SystemRandomEngine());

echo "Item drops:\n";
for ($i = 0; $i < 10; $i++) {
    $result = $rarities->roll();
    echo "  " . $result->value . " (Faces: " . $result->faceCount . ")\n";
}
echo "\n";

// Example 6: Getting available faces
echo "Example 6: Available faces\n";
echo "---------------------------\n";

$actions = new CustomDie(['Attack', 'Defend', 'Dodge', 'Cast Spell', 'Heal'], new SystemRandomEngine());

echo "Available actions:\n";
$faces = $actions->getFaces();
foreach ($faces as $index => $action) {
    echo "  " . ($index + 1) . ": $action\n";
}
echo "\nTotal faces: " . count($faces) . "\n\n";

// Example 7: Decision tree with custom dice
echo "Example 7: NPC behavior\n";
echo "------------------------\n";

class NPC {
    private CustomDie $moodDie;
    private CustomDie $actionDie;

    public function __construct() {
        $this->moodDie = new CustomDie(
            ['Angry', 'Happy', 'Neutral', 'Sad', 'Confused'],
            new SystemRandomEngine()
        );

        $this->actionDie = new CustomDie(
            ['Talk', 'Attack', 'Flee', 'Help', 'Ignore'],
            new SystemRandomEngine()
        );
    }

    public function getRandomBehavior(): string {
        $mood = $this->moodDie->roll();
        $action = $this->actionDie->roll();

        return "NPC is {$mood->value} and will {$action->value}";
    }
}

$npc = new NPC();

echo "NPC behaviors:\n";
for ($i = 0; $i < 8; $i++) {
    echo "  " . $npc->getRandomBehavior() . "\n";
}

echo "\n=== Complete! ===\n";
