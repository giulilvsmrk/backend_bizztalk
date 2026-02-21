<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromocionSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            DB::table('promocion')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'nombre' => "Promo Global $i",
                'codigo_cupon' => "PROMO$i",
                'descripcion' => "Descuento general",
                'valor_descuento' => rand(5,20),
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addMonth(),
                'monto_minimo_compra' => 0,
                'activo' => true,
                'reglas_extra' => '{}',
                'tipo_beneficio' => 'porcentaje',
                'alcance' => 'global'
            ]);
        }
    }
}