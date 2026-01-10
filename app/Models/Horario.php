<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Horario extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'horario';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'dia_semana',
        'hora_apertura',
        'hora_cierre',
        'es_feriado',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
        'es_feriado' => 'boolean',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function getNombreDiaAttribute(): string
    {
        return match ($this->dia_semana) {
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            default => 'Desconocido',
        };
    }

    public function scopeDelDia(Builder $query, int $dia): void
    {
        $query->where('dia_semana', $dia);
    }

    public function scopeAbiertoA(Builder $query, string $hora): void
    {
        $query->where('es_feriado', false)
                ->whereTime('hora_apertura', '<=', $hora)
                ->whereTime('hora_cierre', '>', $hora);
    }

    public function scopeOrdenado(Builder $query): void
    {
        $query->orderBy('dia_semana')
                ->orderBy('hora_apertura');
    }
}
