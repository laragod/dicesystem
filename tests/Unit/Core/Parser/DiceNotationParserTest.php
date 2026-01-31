<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Tests\Unit\Core\Parser;

use Laragod\DiceSystem\Core\Exception\InvalidDieException;
use Laragod\DiceSystem\Core\Exception\InvalidNotationException;
use Laragod\DiceSystem\Core\Parser\DiceExpression;
use Laragod\DiceSystem\Core\Parser\DiceNotationParser;
use PHPUnit\Framework\TestCase;

class DiceNotationParserTest extends TestCase
{
    private DiceNotationParser $parser;

    protected function setUp(): void
    {
        $this->parser = new DiceNotationParser();
    }

    /**
     * @test
     * @dataProvider validNotationProvider
     */
    public function it_parses_valid_dice_notation(string $notation, int $expectedQuantity, int $expectedSides): void
    {
        $expression = $this->parser->parse($notation);

        $this->assertInstanceOf(DiceExpression::class, $expression);
        $this->assertSame($expectedQuantity, $expression->quantity);
        $this->assertSame($expectedSides, $expression->sides);
    }

    /**
     * @return array<string, array{string, int, int}>
     */
    public static function validNotationProvider(): array
    {
        return [
            'standard 2d6' => ['2d6', 2, 6],
            'single d20' => ['1d20', 1, 20],
            'three d8' => ['3d8', 3, 8],
            'large quantity' => ['100d100', 100, 100],
            'edge case 1d2' => ['1d2', 1, 2],
        ];
    }

    /**
     * @test
     * @dataProvider caseInsensitiveProvider
     */
    public function it_parses_case_insensitive_notation(string $notation, int $expectedQuantity, int $expectedSides): void
    {
        $expression = $this->parser->parse($notation);

        $this->assertSame($expectedQuantity, $expression->quantity);
        $this->assertSame($expectedSides, $expression->sides);
    }

    /**
     * @return array<string, array{string, int, int}>
     */
    public static function caseInsensitiveProvider(): array
    {
        return [
            'uppercase D' => ['2D6', 2, 6],
            'lowercase d' => ['2d6', 2, 6],
            'mixed case 1' => ['3D20', 3, 20],
            'mixed case 2' => ['5d10', 5, 10],
        ];
    }

    /**
     * @test
     * @dataProvider invalidNotationProvider
     */
    public function it_throws_exception_for_invalid_notation(string $notation): void
    {
        $this->expectException(InvalidNotationException::class);
        $this->parser->parse($notation);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidNotationProvider(): array
    {
        return [
            'missing quantity' => ['d6'],
            'wrong separator' => ['2x6'],
            'alphabetic' => ['abc'],
            'empty string' => [''],
            'incomplete notation' => ['2d'],
            'only d' => ['d'],
            'zero sides' => ['2d0'],
            'one side die' => ['1d1'],
            'spaces' => ['2 d 6'],
            'negative quantity' => ['-2d6'],
            'negative sides' => ['2d-6'],
            'decimal quantity' => ['2.5d6'],
            'decimal sides' => ['2d6.5'],
        ];
    }

    /**
     * @test
     */
    public function dice_expression_to_string_returns_notation(): void
    {
        $expression = $this->parser->parse('2d6');

        $this->assertSame('2d6', (string) $expression);
    }

    /**
     * @test
     */
    public function dice_expression_to_string_preserves_values(): void
    {
        $expression = $this->parser->parse('3D20');

        $this->assertSame('3d20', (string) $expression);
    }

    /**
     * @test
     */
    public function dice_expression_validates_quantity_minimum(): void
    {
        $this->expectException(InvalidDieException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        new DiceExpression(0, 6);
    }

    /**
     * @test
     */
    public function dice_expression_validates_sides_minimum(): void
    {
        $this->expectException(InvalidDieException::class);
        $this->expectExceptionMessage('Sides must be at least 2');

        new DiceExpression(2, 1);
    }

    /**
     * @test
     */
    public function dice_expression_rejects_one_sided_die(): void
    {
        $this->expectException(InvalidDieException::class);
        $this->expectExceptionMessage('Sides must be at least 2');

        new DiceExpression(1, 1);
    }

    /**
     * @test
     */
    public function dice_expression_accepts_valid_minimum_values(): void
    {
        $expression = new DiceExpression(1, 2);

        $this->assertSame(1, $expression->quantity);
        $this->assertSame(2, $expression->sides);
    }

    /**
     * @test
     */
    public function dice_expression_is_readonly(): void
    {
        $expression = new DiceExpression(2, 6);

        $this->assertSame(2, $expression->quantity);
        $this->assertSame(6, $expression->sides);

        // Verify properties are readonly (this would fail at compile time in PHP 8.1+)
        $reflection = new \ReflectionClass($expression);
        $this->assertTrue($reflection->isReadOnly());
    }
}
