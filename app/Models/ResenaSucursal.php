<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ResenaSucursal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'resena_sucursal';

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_sucursal',
        'id_pedido',
        'puntuacion',
        'comentario',
        'aspectos_positivos',
    ];

    protected $casts = [
        'puntuacion' => 'integer',
        'fecha_registro' => 'datetime',
        'aspectos_positivos' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function getEsVisitaVerificadaAttribute(): bool
    {
        return !is_null($this->id_pedido);
    }

    public function getAutorVisibleAttribute(): string
    {
        return $this->usuario->nombres ?? 'Usuario Desconocido';
    }

    public function scopeDeSucursal(Builder $query, string $sucursalId): void
    {
        $query->where('id_sucursal', $sucursalId);
    }

    public function scopeCriticas(Builder $query): void
    {
        $query->where('puntuacion', '<=', 2);
    }

    public function scopeExcelentes(Builder $query): void
    {
        $query->where('puntuacion', 5);
    }
}
