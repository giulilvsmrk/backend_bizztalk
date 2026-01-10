<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categoria';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_negocio',
        'nombre',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'id_negocio');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_categoria');
    }

    public function scopeDeNegocio(Builder $query, string $negocioId): void
    {
        $query->where('id_negocio', $negocioId);
    }

    public function scopeAlfabetico(Builder $query): void
    {
        $query->orderBy('nombre', 'asc');
    }
}
