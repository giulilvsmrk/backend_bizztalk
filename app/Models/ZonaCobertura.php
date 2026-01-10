<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ZonaCobertura extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasSpatial;

    protected $table = 'zona_cobertura';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'id_sucursal',
        'nombre',
        'area',
        'costo_envio',
        'tiempo_min_extra',
        'activo',
    ];

    protected $casts = [
        'area' => Polygon::class,
        'costo_envio' => 'decimal:2',
        'tiempo_min_extra' => 'integer',
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function scopeQueCubren(Builder $query, Point $ubicacion): void
    {
        $query->whereRaw('ST_Contains(area, ?)', [$ubicacion]);
    }

    public function scopeDeSucursal(Builder $query, string $sucursalId): void
    {
        $query->where('id_sucursal', $sucursalId)
                ->where('activo', true);
    }
}
