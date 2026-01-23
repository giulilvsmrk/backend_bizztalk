<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $idRolAdmin   = DB::table('rol')->where('nombre', 'admin_plataforma')->value('id');
        $idRolCliente = DB::table('rol')->where('nombre', 'cliente')->value('id');
        $idRolDueno   = DB::table('rol')->where('nombre', 'dueno')->value('id');
        $admin = Usuario::factory()->create([
            'nombres' => 'Super',
            'apellidos' => 'Administrador',
            'correo' => 'admin@biztalk.com',
            'telefono' => '+59100000000',
        ]);

        DB::table('usuario_rol')->insert([
            'id_usuario' => $admin->id,
            'id_rol' => $idRolAdmin,
            'fecha_asignacion' => now(),
        ]);

        Usuario::factory(10)->create()->each(function ($usuario) use ($idRolCliente) {
            DB::table('usuario_rol')->insert([
                'id_usuario' => $usuario->id,
                'id_rol' => $idRolCliente,
                'fecha_asignacion' => now(),
            ]);
        });

        Usuario::factory(2)->create()->each(function ($usuario) use ($idRolDueno) {
            DB::table('usuario_rol')->insert([
                'id_usuario' => $usuario->id,
                'id_rol' => $idRolDueno,
                'fecha_asignacion' => now(),
            ]);
        });
    }
}
