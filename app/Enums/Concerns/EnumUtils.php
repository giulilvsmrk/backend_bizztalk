<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

trait EnumUtils
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_combine(
            self::values(),
            array_map(fn($case) => $case->name, self::cases())
        );
    }
}
