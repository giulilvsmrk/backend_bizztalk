<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrito extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'carrito';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = true;
    const CREATED_AT = null;
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_usuario',
        'id_sucursal_activa',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'fecha_actualizacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function sucursalActiva(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_activa');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemCarrito::class, 'id_carrito');
    }

    public function calcularSubtotal(): float
    {
        // Solo cargamos items y producto, eliminamos la relación 'sucursales'
        $this->load(['items.producto']);

        $total = 0.0;

        foreach ($this->items as $item) {
            $precio = $item->producto->precio_base;

            if ($this->id_sucursal_activa) {
                $inventario = $item->producto->registrosInventario
                    ->where('id_sucursal', $this->id_sucursal_activa)
                    ->first();

                if ($inventario) {
                    $precio = $inventario->precio_local ?? $precio;
                }
            }

            $total += $precio * $item->cantidad;
        }

        return $total;
    }

    public function vaciar(): void
    {
        $this->items()->delete();
        $this->touch();
    }

    public function estaVacio(): bool
    {
        return $this->items()->count() === 0;
    }
}
