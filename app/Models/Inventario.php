<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Inventario extends Pivot
{
    use HasFactory, HasUuids;

    protected $table = 'inventario';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    const CREATED_AT = null;
    const UPDATED_AT = 'ultima_actualizacion';

    protected $fillable = [
        'id_sucursal',
        'id_producto',
        'cantidad',
        'precio_local',
        'activo',
        'ultima_actualizacion'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_local' => 'decimal:2',
        'activo' => 'boolean',
        'ultima_actualizacion' => 'datetime',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function scopeDisponible(Builder $query): void
    {
        $query->where('activo', true)
                ->where('cantidad', '>', 0);
    }

    public function scopeAgotado(Builder $query): void
    {
        $query->where('cantidad', 0);
    }

    public function scopeBajoStock(Builder $query, int $umbral = 5): void
    {
        $query->where('activo', true)
                ->where('cantidad', '<=', $umbral)
                ->where('cantidad', '>', 0);
    }

    public function tieneStockSuficiente(int $cantidadRequerida): bool
    {
        return $this->activo && $this->cantidad >= $cantidadRequerida;
    }

    public function getPrecioVentaAttribute(): float
    {
        return (float) ($this->precio_local ?? $this->producto->precio_base);
    }
}
