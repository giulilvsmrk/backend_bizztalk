<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class GaleriaSucursal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'galeria_sucursal';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_subida';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_sucursal',
        'url',
        'descripcion',
        'orden_visual',
    ];

    protected $casts = [
        'orden_visual' => 'integer',
        'fecha_subida' => 'datetime',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function scopeOrdenado(Builder $query): void
    {
        $query->orderBy('orden_visual', 'asc')
                ->orderByDesc('fecha_subida');
    }

    public function scopeDeSucursal(Builder $query, string $sucursalId): void
    {
        $query->where('id_sucursal', $sucursalId);
    }
}
