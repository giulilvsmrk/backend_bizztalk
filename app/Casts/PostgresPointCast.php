<?php

declare(strict_types=1);

namespace App\Casts;

use App\Values\PostgresPoint;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PostgresPointCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?PostgresPoint
    {
        if (is_null($value)) {
            return null;
        }

        return PostgresPoint::fromSql((string) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof PostgresPoint) {
            return $value->toSql();
        }

        if (is_array($value)) {
            return sprintf('(%F,%F)', $value['lng'], $value['lat']);
        }

        return (string) $value;
    }
}
