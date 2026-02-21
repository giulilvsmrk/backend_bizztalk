<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigEntregaSeeder extends Seeder
{
    public function run(): void
    {
        $sucursal = DB::table('sucursal')->first();

        DB::table('config_entrega')->insert([
            'id' => DB::raw('gen_random_uuid()'),
            'id_sucursal' => $sucursal->id,
            'tipo' => 'delivery',
            'costo_base' => 0,
            'vehiculos_permitidos' => json_encode(['moto'])
        ]);
    }
}