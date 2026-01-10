<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class HistorialVista extends Model
{
    use HasFactory, HasSpatial;

    protected $table = 'historial_vista';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    const CREATED_AT = 'fecha_vista';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_producto',
        'ubicacion_viewer',
    ];

    protected $casts = [
        'fecha_vista' => 'datetime',
        'ubicacion_viewer' => Point::class,
        'id' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function scopeRecientesDe(Builder $query, string $usuarioId): void
    {
        $query->where('id_usuario', $usuarioId)
                ->orderByDesc('fecha_vista')
                ->select('id_producto', 'fecha_vista')
                ->distinct('id_producto');
    }

    public static function registrar(string $userId, string $productoId, ?Point $ubicacion = null): void
    {
        static::create([
            'id_usuario' => $userId,
            'id_producto' => $productoId,
            'ubicacion_viewer' => $ubicacion,
            'fecha_vista' => now(),
        ]);
    }
}
