<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Producto extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'producto';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'id_negocio',
        'id_categoria',
        'nombre',
        'descripcion',
        'imagen_url',
        'precio_base',
        'activo',
    ];

    protected $hidden = [
        'vector_busqueda',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'id_negocio');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    // Ahora el inventario es directo por producto
    public function registrosInventario(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_producto');
    }

    public function galeria(): HasMany
    {
        return $this->hasMany(GaleriaProducto::class, 'id_producto')
                    ->orderBy('orden_visual');
    }

    public function promociones(): HasMany
    {
        return $this->hasMany(Promocion::class, 'id_producto');
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(ResenaProducto::class, 'id_producto');
    }

    public function enListasDeseos(): HasMany
    {
        return $this->hasMany(ListaDeseos::class, 'id_producto');
    }

    public function vistas(): HasMany
    {
        return $this->hasMany(HistorialVista::class, 'id_producto');
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $query->whereRaw(
            "vector_busqueda @@ websearch_to_tsquery('spanish', ?)",
            [$term]
        )->orderByRaw(
            "ts_rank(vector_busqueda, websearch_to_tsquery('spanish', ?)) DESC",
            [$term]
        );
    }

    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }
}
