<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CanalVenta;
use App\Enums\EstadoPedido;
use App\Enums\TipoEntrega;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Pedido extends Model
{
    use HasFactory, HasUuids, HasSpatial;

    protected $table = 'pedido';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_sucursal',
        'id_usuario',
        'importe_subtotal',
        'importe_envio',
        'importe_descuento',
        'importe_total',
        'estado',
        'canal',
        'tipo_entrega',
        'ubicacion_entrega',
        'direccion_texto',
        'fecha_programada',
    ];

    protected $casts = [
        'importe_subtotal' => 'decimal:2',
        'importe_envio' => 'decimal:2',
        'importe_descuento' => 'decimal:2',
        'importe_total' => 'decimal:2',
        'fecha_programada' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
        'ubicacion_entrega' => Point::class,
        'estado' => EstadoPedido::class,
        'canal' => CanalVenta::class,
        'tipo_entrega' => TipoEntrega::class,
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialPedido::class, 'id_pedido')->orderBy('fecha_cambio', 'desc');
    }

    public function transacciones(): HasMany
    {
        return $this->hasMany(TransaccionPago::class, 'id_pedido');
    }

    public function resenaSucursal(): HasOne
    {
        return $this->hasOne(ResenaSucursal::class, 'id_pedido');
    }

    public function usosPromocion(): HasMany
    {
        return $this->hasMany(UsoPromocion::class, 'id_pedido');
    }

    public function logsIA(): HasMany
    {
        return $this->hasMany(LogInteraccionIa::class, 'id_pedido');
    }

    public function esFinalizado(): bool
    {
        return in_array($this->estado, [
            EstadoPedido::ENTREGADO,
            EstadoPedido::CANCELADO
        ], true);
    }

    public function estaPagado(): bool
    {
        return $this->estado === EstadoPedido::PAGADO
            || $this->estado === EstadoPedido::PREPARANDO
            || $this->estado === EstadoPedido::EN_CAMINO
            || $this->estado === EstadoPedido::ENTREGADO;
    }

    public function scopeActivos(Builder $query): void
    {
        $query->whereNotIn('estado', [EstadoPedido::ENTREGADO, EstadoPedido::CANCELADO]);
    }

    public function scopeDeHoy(Builder $query): void
    {
        $query->whereDate('fecha_creacion', now()->toDateString());
    }

    public function scopePorNumeroOrden(Builder $query, int $numero): void
    {
        $query->where('numero_orden_publico', $numero);
    }
}
