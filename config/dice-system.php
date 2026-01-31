<?php
declare(strict_types=1);

use Laragod\DiceSystem\Enum\RollerDriver;

return [
    'driver' => env('DICE_SYSTEM_DRIVER', RollerDriver::Dhondt2d6->value),

    // Seed persistence:
    // - seed is generated if missing and persisted in a file under storage/app
    // - visible in logs on boot
    'seed' => [
        'path' => env('DICE_SYSTEM_SEED_PATH', 'dice-system/seed.txt'),
        'log'  => (bool) env('DICE_SYSTEM_SEED_LOG', true),
    ],

    'deck' => [
        'multiplier' => (int) env('DICE_SYSTEM_DECK_MULTIPLIER', 1),
    ],

    'double_others' => [
        'multiplier' => (float) env('DICE_SYSTEM_DOUBLE_OTHERS_MULTIPLIER', 2.0),
    ],

    'dhondt' => [
        'windowSize' => env('DICE_SYSTEM_DHONDT_WINDOW', 200), // int or null; env gives string
    ],
];
