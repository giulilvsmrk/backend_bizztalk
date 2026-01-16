<?php

declare(strict_types=1);

namespace App\Values;

use Illuminate\Contracts\Database\Eloquent\Castable;
use App\Casts\PostgresPointCast;
use JsonSerializable;

class PostgresPoint implements Castable, JsonSerializable
{
    public function __construct(
        public float $latitude,
        public float $longitude
    ) {}

    function toSql(): string
    {
        return sprintf('(%F,%F)', $this->longitude, $this->latitude);
    }

    public static function fromSql(string $value): self
    {
        $content = trim($value, '()');

        $coords = explode(',', $content);

        return new self(
            latitude: (float) ($coords[1] ?? 0),
            longitude: (float) ($coords[0] ?? 0)
        );
    }

    public static function castUsing(array $arguments): string
    {
        return PostgresPointCast::class;
    }

    public function jsonSerialize(): array
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }
}
