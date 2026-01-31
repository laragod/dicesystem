<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use DateTimeImmutable;
use Laragod\DiceSystem\Core\Contract\RandomEngineInterface;
use Laragod\DiceSystem\Core\Exception\InvalidDieException;

/**
 * CustomDie represents a die with custom non-numeric faces.
 * It uses a RandomEngineInterface to generate random values.
 *
 * @template T
 */
final class CustomDie
{
    /**
     * @var array<T>
     */
    private readonly array $faces;

    /**
     * @param array<T> $faces The custom face values for this die
     * @param RandomEngineInterface $engine The random engine to use for rolling
     * @throws InvalidDieException If faces array is empty
     */
    public function __construct(
        array $faces,
        private RandomEngineInterface $engine,
    ) {
        if (empty($faces)) {
            throw new InvalidDieException(
                'CustomDie must have at least one face'
            );
        }

        // Store immutable copy of faces array
        $this->faces = array_values($faces);
    }

    /**
     * Roll the die and return the result with a randomly selected face value.
     *
     * @return CustomDieResult<T> The result of the roll with metadata
     */
    public function roll(): CustomDieResult
    {
        $faceCount = count($this->faces);
        $index = $this->engine->next(0, $faceCount - 1);

        return new CustomDieResult(
            value: $this->faces[$index],
            faceCount: $faceCount,
            rolledAt: new DateTimeImmutable(),
            seed: $this->engine->getSeed(),
        );
    }

    /**
     * Get all face values for this die.
     *
     * @return array<T> A copy of the faces array
     */
    public function getFaces(): array
    {
        return $this->faces;
    }
}
