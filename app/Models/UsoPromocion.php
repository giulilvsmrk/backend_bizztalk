<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class UsoPromocion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'uso_promocion';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    const CREATED_AT = 'fecha_uso';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_promocion',
        'id_usuario',
        'id_pedido',
        'monto_ahorrado',
    ];

    protected $casts = [
        'monto_ahorrado' => 'decimal:2',
        'fecha_uso' => 'datetime',
    ];

    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class, 'id_promocion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function scopeVecesUsadoPor(Builder $query, string $promocionId, string $usuarioId): void
    {
        $query->where('id_promocion', $promocionId)
                ->where('id_usuario', $usuarioId);
    }

    public function scopeAhorroTotalUsuario(Builder $query, string $usuarioId): void
    {
        $query->where('id_usuario', $usuarioId);
    }

    public function scopeDescuentosDeSucursal(Builder $query, string $sucursalId): void
    {
        $query->whereHas('pedido', function ($q) use ($sucursalId) {
            $q->where('id_sucursal', $sucursalId);
        });
    }
}
