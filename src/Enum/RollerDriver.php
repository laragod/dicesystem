<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Enum;

enum RollerDriver: string
{
    case Standard2d6   = 'standard_2d6';
    case Deck2d6       = 'deck_2d6';
    case DoubleOthers  = 'double_others';
    case Dhondt2d6     = 'dhondt_2d6';
}
