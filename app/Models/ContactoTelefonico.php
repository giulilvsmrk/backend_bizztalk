<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoContacto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ContactoTelefonico extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contacto_telefonico';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'numero',
        'tipo',
        'etiqueta',
        'es_principal',
        'id_negocio',
        'id_sucursal',
    ];

    protected $casts = [
        'tipo' => TipoContacto::class,
        'es_principal' => 'boolean',
        'fecha_creacion' => 'datetime',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'id_negocio');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function scopePrincipal(Builder $query): void
    {
        $query->where('es_principal', true);
    }

    public function scopePorTipo(Builder $query, TipoContacto $tipo): void
    {
        $query->where('tipo', $tipo);
    }

    public function scopeCorporativos(Builder $query): void
    {
        $query->whereNotNull('id_negocio')
                ->whereNull('id_sucursal');
    }

    public function scopeDeSucursal(Builder $query, string $sucursalId): void
    {
        $query->where('id_sucursal', $sucursalId);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($contacto) {
            if ($contacto->id_negocio && $contacto->id_sucursal) {
                // En una app real, podrías lanzar una excepción o limpiar uno de los dos
                // throw new \Exception("Un contacto no puede pertenecer a Negocio y Sucursal simultáneamente.");
            }
        });
    }
}
