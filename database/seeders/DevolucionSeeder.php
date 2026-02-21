<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevolucionSeeder extends Seeder
{
    public function run(): void
    {
        $pedido = DB::table('pedido')->first();

        DB::table('devolucion')->insert([
            'id' => DB::raw('gen_random_uuid()'),
            'id_pedido' => $pedido->id,
            'motivo' => 'Producto incorrecto',
            'estado' => 'pendiente'
        ]);
    }
}