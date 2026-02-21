<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistorialEstadoPedidoSeeder extends Seeder
{
    public function run(): void
    {
        $pedido = DB::table('pedido')->first();

        DB::table('historial_estado_pedido')->insert([
            'id' => DB::raw('gen_random_uuid()'),
            'id_pedido' => $pedido->id,
            'estado' => 'confirmado',
            'fecha' => now()
        ]);
    }
}