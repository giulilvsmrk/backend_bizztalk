<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class DireccionUsuario extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasSpatial;

    protected $table = 'direccion_usuario';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'id_usuario',
        'etiqueta',
        'ubicacion_gps',
        'direccion_texto',
        'referencia',
        'es_predeterminada',
        'ultima_fecha_uso',
    ];

    protected $casts = [
        'ubicacion_gps' => Point::class,
        'es_predeterminada' => 'boolean',
        'ultima_fecha_uso' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function scopeMasUsadas($query)
    {
        return $query->orderByDesc('ultima_fecha_uso');
    }

    public function scopePredeterminada($query)
    {
        return $query->where('es_predeterminada', true);
    }
}
