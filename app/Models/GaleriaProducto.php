<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class GaleriaProducto extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'galeria_producto';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_subida';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_producto',
        'url',
        'orden_visual',
    ];

    protected $casts = [
        'orden_visual' => 'integer',
        'fecha_subida' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function scopeOrdenado(Builder $query): void
    {
        $query->orderBy('orden_visual', 'asc')
                ->orderByDesc('fecha_subida');
    }
}
