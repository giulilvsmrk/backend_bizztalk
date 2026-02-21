<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonaCoberturaSeeder extends Seeder
{
    public function run(): void
    {
        $negocio = DB::table('negocio')->first();

        DB::statement("
            INSERT INTO zona_cobertura
            (id, id_negocio, nombre, area)
            VALUES (
                gen_random_uuid(),
                '{$negocio->id}',
                'Zona Norte',
                '((-68.12,-16.49),(-68.10,-16.49),(-68.10,-16.47),(-68.12,-16.47))'
            )
        ");
    }
}