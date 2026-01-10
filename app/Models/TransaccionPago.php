<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TransaccionPago extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'transaccion_pago';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_intento';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_pedido',
        'tipo_metodo',
        'id_metodo_guardado',
        'monto_total',
        'moneda',
        'estado',
        'fecha_confirmacion',
        'id_referencia_banco',
        'qr_imagen_url',
        'metadata_banco',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'fecha_intento' => 'datetime',
        'fecha_confirmacion' => 'datetime',
        'metadata_banco' => 'array',
        'estado' => EstadoTransaccion::class,
        'tipo_metodo' => MetodoPago::class,
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function metodoGuardado(): BelongsTo
    {
        return $this->belongsTo(BilleteraUsuario::class, 'id_metodo_guardado');
    }

    public function marcarComoAprobada(string $referenciaBanco = null): void
    {
        $this->update([
            'estado' => EstadoTransaccion::APROBADO, //
            'fecha_confirmacion' => now(),
            'id_referencia_banco' => $referenciaBanco ?? $this->id_referencia_banco,
        ]);
    }

    public function marcarComoRechazada(array $motivo = []): void
    {
        $meta = $this->metadata_banco ?? [];
        $meta['error_rechazo'] = $motivo;

        $this->update([
            'estado' => EstadoTransaccion::RECHAZADO,
            'metadata_banco' => $meta,
        ]);
    }

    public function scopeAprobadas(Builder $query): void
    {
        $query->where('estado', EstadoTransaccion::APROBADO);
    }

    public function scopePendientes(Builder $query): void
    {
        $query->where('estado', EstadoTransaccion::PENDIENTE);
    }
}
