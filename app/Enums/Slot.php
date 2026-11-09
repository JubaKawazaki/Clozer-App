<?php

namespace App\Enums;

/** Les rangées Build de Closet (web/src/api.ts CATEGORIES). */
enum Slot: string
{
    case Headwear = 'headwear';
    case Outerwear = 'outerwear';
    case Top = 'top';
    case Bottom = 'bottom';
    case Shoes = 'shoes';

    /** Slots acceptant plusieurs couches (3 max). */
    public static function layered(): array
    {
        return [self::Outerwear, self::Top];
    }
}
