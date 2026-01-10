<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Coleccion extends Model
{
    use HasFactory, HasUuids;
    
    protected $table = 'coleccion';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_cover_url',
        'id_usuario_destino',
        'es_generada_por_ia',
        'activo',
        'fecha_expiracion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'es_generada_por_ia' => 'boolean',
        'fecha_expiracion' => 'datetime',
        'fecha_creacion' => 'datetime',
    ];

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(
            Producto::class,
            'item_coleccion',
            'id_coleccion',
            'id_producto'
        )->withPivot(['orden_visual', 'score_relevancia']);
    }

    public function esVigente(): bool
    {
        if (!$this->activo) return false;

        $ahora = now();

        if ($this->fecha_expiracion && $ahora->gt($this->fecha_expiracion)) {
            return false;
        }

        return true;
    }

    public function scopeVisibles(Builder $query): void
    {
        $now = now();

        $query->where('activo', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('fecha_expiracion')->orWhere('fecha_expiracion', '>=', $now);
                });
    }

    public function scopePermanentes(Builder $query): void
    {
        $query->whereNull('fecha_expiracion');
    }
}
