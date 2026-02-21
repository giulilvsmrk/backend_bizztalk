<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class NegocioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = DB::table('usuario')->pluck('id')->toArray();

        for ($i = 1; $i <= 20; $i++) {
            DB::table('negocio')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'id_propietario' => $usuarios[array_rand($usuarios)],
                'nombre' => "Negocio $i",
                'nit' => "NIT$i",
                'descripcion' => "Descripcion negocio $i",
                'activo' => true,
                'direccion_texto' => "Direccion $i",
                'ubicacion_gps' => DB::raw("point(-68.$i, -16.$i)")
            ]);
        }
    }
}