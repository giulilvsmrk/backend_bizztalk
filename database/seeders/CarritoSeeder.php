<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarritoSeeder extends Seeder
{
    public function run(): void
    {
        $usuario = DB::table('usuario')->first();
        $producto = DB::table('producto')->first();

        $carritoId = DB::table('carrito')->insertGetId([
            'id' => DB::raw('gen_random_uuid()'),
            'id_usuario' => $usuario->id
        ], 'id');

        DB::table('carrito_detalle')->insert([
            'id_carrito' => $carritoId,
            'id_producto' => $producto->id,
            'cantidad' => 2
        ]);
    }
}