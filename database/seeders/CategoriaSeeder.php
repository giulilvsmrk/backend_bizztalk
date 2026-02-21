<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $negocios = DB::table('negocio')->pluck('id')->toArray();

        for ($i = 1; $i <= 20; $i++) {
            DB::table('categoria')->insert([
                'id_negocio' => $negocios[array_rand($negocios)],
                'nombre' => "Categoria $i"
            ]);
        }
    }
}