<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Factory;

use InvalidArgumentException;
use Laragod\DiceSystem\Contract\DiceRollerInterface;
use Laragod\DiceSystem\Contract\SeedObserverInterface;
use Laragod\DiceSystem\Contract\SeedProviderInterface;
use Laragod\DiceSystem\Enum\RollerDriver;
use Laragod\DiceSystem\Random\MtRandomSource;
use Laragod\DiceSystem\Roller\DeckDiceRoller;
use Laragod\DiceSystem\Roller\Dhondt2d6Roller;
use Laragod\DiceSystem\Roller\DoubleOthersRoller;
use Laragod\DiceSystem\Roller\Standard2d6Roller;
use Laragod\DiceSystem\Support\WeightedPicker;

class RollerFactory
{
    public function __construct(
        protected SeedProviderInterface $seedProvider,
        protected ?SeedObserverInterface $seedObserver = null,
        protected WeightedPicker $picker = new WeightedPicker(),
    ) {}

    /**
     * @param RollerDriver|string $driver
     * @param array<string,mixed> $options
     */
    public function make(RollerDriver|string $driver, array $options = []): DiceRollerInterface
    {
        $driver = is_string($driver) ? RollerDriver::from($driver) : $driver;

        $scope = (string)($options['scope'] ?? $driver->value);
        $seed  = $this->seedProvider->seedFor($scope);

        $this->seedObserver?->onSeedResolved($scope, $seed, [
            'driver' => $driver->value,
        ]);

        $rng = new MtRandomSource($seed);

        return match ($driver) {
            RollerDriver::Standard2d6 => new Standard2d6Roller($rng),

            RollerDriver::Deck2d6 => new DeckDiceRoller(
                $rng,
                (int)($options['multiplier'] ?? 1),
            ),

            RollerDriver::DoubleOthers => new DoubleOthersRoller(
                $rng,
                $this->picker,
                (float)($options['multiplier'] ?? 2.0),
            ),

            RollerDriver::Dhondt2d6 => new Dhondt2d6Roller(
                $rng,
                $this->picker,
                array_key_exists('windowSize', $options)
                    ? ($options['windowSize'] === null ? null : (int)$options['windowSize'])
                    : 200,
            ),

            default => throw new InvalidArgumentException("Unknown driver: {$driver->value}"),
        };
    }
}
