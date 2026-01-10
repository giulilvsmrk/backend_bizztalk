<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoEntrega;
use App\Enums\TipoVehiculo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ConfigEntrega extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'config_entrega';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'tipo',
        'alias_personalizado',
        'costo_base',
        'tiempo_min_aprox',
        'vehiculos_permitidos',
        'activo',
    ];

    protected $casts = [
        'tipo' => TipoEntrega::class,
        'costo_base' => 'decimal:2',
        'tiempo_min_aprox' => 'integer',
        'activo' => 'boolean',
        'vehiculos_permitidos' => 'array',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function permiteVehiculo(TipoVehiculo|string $vehiculo): bool
    {
        $valor = $vehiculo instanceof TipoVehiculo ? $vehiculo->value : $vehiculo;
        return in_array($valor, $this->vehiculos_permitidos ?? [], true);
    }

    public function agregarVehiculo(TipoVehiculo $vehiculo): void
    {
        $actuales = $this->vehiculos_permitidos ?? [];

        if (!in_array($vehiculo->value, $actuales, true)) {
            $actuales[] = $vehiculo->value;
            $this->vehiculos_permitidos = $actuales;
        }
    }

    public function scopeActivas(Builder $query): void
    {
        $query->where('activo', true);
    }

    public function scopePorTipo(Builder $query, TipoEntrega $tipo): void
    {
        $query->where('tipo', $tipo);
    }

    public function scopeQueAdmitanVehiculo(Builder $query, string $vehiculo): void
    {
        $query->whereRaw('vehiculos_permitidos @> ?', [json_encode([$vehiculo])]);
    }
}
