<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoBeneficio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Promocion extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'promocion';

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'id_sucursal',
        'id_producto',
        'nombre',
        'descripcion',
        'tipo_beneficio',
        'alcance',
        'valor_descuento',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'codigo_cupon',
        'monto_minimo_compra',
        'reglas_extra'
    ];

    protected $casts = [
        'tipo_beneficio' => TipoBeneficio::class,
        'valor_descuento' => 'decimal:2',
        'monto_minimo_compra' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
        'reglas_extra' => 'array',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function usos(): HasMany
    {
        return $this->hasMany(UsoPromocion::class, 'id_promocion');
    }

    public function esValida(): bool
    {
        $ahora = now();

        if (!$this->activo) return false;
        if ($this->fecha_inicio && $ahora->lt($this->fecha_inicio)) return false;
        if ($this->fecha_fin && $ahora->gt($this->fecha_fin)) return false;
        return true;
    }

    public function calcularDescuento(float $montoBase): float
    {
        return match ($this->tipo_beneficio) {
            TipoBeneficio::PORCENTAJE => $montoBase * ($this->valor_descuento / 100),
            TipoBeneficio::MONTO_FIJO => min($this->valor_descuento, $montoBase),
            TipoBeneficio::ENVIO_GRATIS => 0,
            default => 0.0,
        };
    }

    public function scopeVigentes(Builder $query): void
    {
        $now = now();
        $query->where('activo', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $now);
                });
    }

    public function scopeGlobales(Builder $query): void
    {
        $query->whereNull('id_sucursal');
        // Opcional: Validar alcance explícito
        // ->where('alcance', 'global');
    }

    public function scopeParaSucursal(Builder $query, string $sucursalId): void
    {
        $query->where(function ($q) use ($sucursalId) {
            $q->where('id_sucursal', $sucursalId)
                ->orWhereNull('id_sucursal');
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($promocion) {
            if (empty($promocion->alcance)) {
                if ($promocion->id_producto) {
                    $promocion->alcance = 'producto';
                } elseif ($promocion->id_sucursal) {
                    $promocion->alcance = 'sucursal';
                } else {
                    $promocion->alcance = 'global';
                }
            }
        });
    }
}
