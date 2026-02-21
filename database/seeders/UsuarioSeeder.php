<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            DB::table('usuario')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'nombres' => "Usuario$i",
                'apellidos' => "Apellido$i",
                'correo' => "user$i@test.com",
                'password_hash' => Hash::make('123456'),
                'telefono' => '700000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'activo' => true
            ]);
        }
    }
}