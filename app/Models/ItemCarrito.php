<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCarrito extends Model
{
    use HasFactory;

    protected $table = 'item_carrito';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    const CREATED_AT = 'fecha_agregado';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_carrito',
        'id_producto',
        'cantidad',
        'observacion',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha_agregado' => 'datetime',
    ];

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'id_carrito');
    }

  public function producto(): BelongsTo
 {
    return $this->belongsTo(Producto::class, 'id_producto')->withTrashed();
 }
    public function getPrecioUnitarioEstimadoAttribute(): float
    {
        $idSucursal = $this->carrito->id_sucursal_activa ?? null;

        if ($idSucursal && $this->relationLoaded('producto')) {
            $inventario = $this->producto->registrosInventario
                ->where('id_sucursal', $idSucursal)
                ->first();

            if ($inventario && $inventario->precio_local) {
                return (float) $inventario->precio_local;
            }
        }

        return (float) ($this->producto->precio_base ?? 0);
    }

    public function getSubtotalEstimadoAttribute(): float
    {
        return $this->cantidad * $this->precio_unitario_estimado;
    }
}
