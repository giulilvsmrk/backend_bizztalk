<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $negocios = DB::table('negocio')->pluck('id')->toArray();
        $categorias = DB::table('categoria')->pluck('id')->toArray();

        for ($i = 1; $i <= 30; $i++) {
            DB::table('producto')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'id_negocio' => $negocios[array_rand($negocios)],
                'id_categoria' => $categorias[array_rand($categorias)],
                'nombre' => "Producto $i",
                'descripcion' => "Descripcion producto $i",
                'precio_base' => rand(10, 200),
                'activo' => true
            ]);
        }
    }
}