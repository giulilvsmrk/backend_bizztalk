<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $productos = DB::table('producto')->pluck('id');

        foreach ($productos as $producto) {
            DB::table('inventario')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'id_producto' => $producto,
                'cantidad' => rand(5, 100),
                'precio_local' => rand(10, 200),
                'activo' => true
            ]);
        }
    }
}