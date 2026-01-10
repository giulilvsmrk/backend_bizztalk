<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ResenaProducto extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'resena_producto';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_producto',
        'id_detalle_pedido',
        'puntuacion',
        'comentario',
    ];

    protected $casts = [
        'puntuacion' => 'integer',
        'fecha_registro' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function detallePedido(): BelongsTo
    {
        return $this->belongsTo(DetallePedido::class, 'id_detalle_pedido');
    }

    public function getEsCompraVerificadaAttribute(): bool
    {
        return !is_null($this->id_detalle_pedido);
    }

    public function getNombreAutorAttribute(): string
    {
        return $this->usuario->nombres ?? 'Usuario Eliminado';
    }

    public function scopePositivas(Builder $query): void
    {
        $query->where('puntuacion', '>=', 4);
    }

    public function scopeNegativas(Builder $query): void
    {
        $query->where('puntuacion', '<=', 2);
    }

    public function scopeConComentario(Builder $query): void
    {
        $query->whereNotNull('comentario')
                ->where('comentario', '!=', '');
    }
}
