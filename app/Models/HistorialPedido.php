<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPedido;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPedido extends Model
{
    use HasFactory;

    protected $table = 'historial_pedido';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    const CREATED_AT = 'fecha_cambio';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_pedido',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'id_usuario_operador',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
        'estado_anterior' => EstadoPedido::class,
        'estado_nuevo' => EstadoPedido::class,
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_operador');
    }

    public function getDescripcionCambioAttribute(): string
    {
        $anterior = $this->estado_anterior?->value ?? 'Inicio';
        $nuevo = $this->estado_nuevo->value;

        return sprintf("Cambio de %s a %s", ucfirst($anterior), ucfirst($nuevo));
    }
}
