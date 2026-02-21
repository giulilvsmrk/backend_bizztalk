<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = DB::table('usuario')->pluck('id')->toArray();

        for ($i = 1; $i <= 20; $i++) {
            DB::table('pedido')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'id_sucursal' => DB::raw("(SELECT id FROM sucursal LIMIT 1)"),
                'id_usuario' => $usuarios[array_rand($usuarios)],
                'importe_subtotal' => 100,
                'importe_envio' => 10,
                'importe_descuento' => 5,
                'importe_total' => 105,
                'direccion_texto' => "Direccion test",
                'canal' => 'app',
                'tipo_entrega' => 'delivery',
                'estado' => 'pendiente'
            ]);
        }
    }
}