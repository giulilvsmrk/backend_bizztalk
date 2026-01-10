<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ItemGuardado extends Model
{
    use HasFactory;

    protected $table = 'item_guardado';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    const CREATED_AT = 'fecha_guardado';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_producto',
        'id_sucursal_origen',
        'cantidad',
        'observacion',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha_guardado' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_origen');
    }

    public function estaDisponible(): bool
    {
        $inventario = $this->producto->registrosInventario
            ->where('id_sucursal', $this->id_sucursal_origen)
            ->first();

        return $inventario && $inventario->cantidad >= $this->cantidad && $inventario->activo;
    }
    
    public function convertirParaCarrito(): array
    {
        return [
            'id_producto' => $this->id_producto,
            'cantidad' => $this->cantidad,
            'observacion' => $this->observacion,
        ];
    }
}
