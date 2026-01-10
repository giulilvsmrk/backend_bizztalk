<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ItemColeccion extends Pivot
{
    use HasFactory;

    protected $table = 'item_coleccion';
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_coleccion',
        'id_producto',
        'orden_visual',
        'score_relevancia'
    ];

    protected $casts = [
        'orden_visual' => 'integer',
        'score_relevancia' => 'decimal:4',
        'id_coleccion' => 'string',
        'id_producto' => 'string',
    ];

    public function coleccion(): BelongsTo
    {
        return $this->belongsTo(Coleccion::class, 'id_coleccion');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function scopeOrdenado(Builder $query): void
    {
        $query->orderBy('orden_visual', 'asc');
    }

    public function scopeDeColeccion(Builder $query, string $coleccionId): void
    {
        $query->where('id_coleccion', $coleccionId);
    }

    public static function actualizarOrden(string $coleccionId, array $ordenProductos): void
    {
        foreach ($ordenProductos as $index => $productoId) {
            static::where('id_coleccion', $coleccionId)
                    ->where('id_producto', $productoId)
                    ->update(['orden_visual' => $index + 1]);
        }
    }
}
