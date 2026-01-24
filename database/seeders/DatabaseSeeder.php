<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Negocio;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Promocion;
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

        // Datos de prueba para Negocios - Un solo Market
        $market = Negocio::create([
            'id' => fake()->uuid(),
            'id_propietario' => Usuario::where('correo', 'admin@biztalk.com')->first()->id,
            'nombre' => 'SuperMarket La Avenida',
            'nit' => '1234567890',
            'descripcion' => 'SuperMarket con la mejor variedad de productos frescos y abarrotes.',
            'logotipo_url' => 'https://via.placeholder.com/300',
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        // Categorías del Market
        $categorias = [
            ['nombre' => 'Frutas y Verduras', 'id_negocio' => $market->id],
            ['nombre' => 'Lácteos', 'id_negocio' => $market->id],
            ['nombre' => 'Carnes', 'id_negocio' => $market->id],
            ['nombre' => 'Bebidas', 'id_negocio' => $market->id],
            ['nombre' => 'Abarrotes', 'id_negocio' => $market->id],
        ];
        
        foreach ($categorias as $cat) {
            DB::table('categoria')->insert($cat);
        }

        // Obtener IDs de categorías
        $catFruta = DB::table('categoria')->where('nombre', 'Frutas y Verduras')->where('id_negocio', $market->id)->first()->id;
        $catLacteos = DB::table('categoria')->where('nombre', 'Lácteos')->where('id_negocio', $market->id)->first()->id;
        $catCarnes = DB::table('categoria')->where('nombre', 'Carnes')->where('id_negocio', $market->id)->first()->id;
        $catBebidas = DB::table('categoria')->where('nombre', 'Bebidas')->where('id_negocio', $market->id)->first()->id;
        $catAbarrotes = DB::table('categoria')->where('nombre', 'Abarrotes')->where('id_negocio', $market->id)->first()->id;

        // Productos reales del Market
        $productos = [
            // Frutas y Verduras
            ['id_negocio' => $market->id, 'id_categoria' => $catFruta, 'nombre' => 'Manzanas Rojas', 'descripcion' => 'Manzanas frescas de la región', 'precio_base' => 2.50, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catFruta, 'nombre' => 'Plátanos', 'descripcion' => 'Plátanos de excelente calidad', 'precio_base' => 1.20, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catFruta, 'nombre' => 'Tomates', 'descripcion' => 'Tomates frescos y rojos', 'precio_base' => 1.80, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catFruta, 'nombre' => 'Lechuga', 'descripcion' => 'Lechuga verde fresca', 'precio_base' => 1.50, 'imagen_url' => 'https://via.placeholder.com/200'],
            // Lácteos
            ['id_negocio' => $market->id, 'id_categoria' => $catLacteos, 'nombre' => 'Leche Entera', 'descripcion' => 'Leche fresca de 1 litro', 'precio_base' => 2.00, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catLacteos, 'nombre' => 'Yogur Natural', 'descripcion' => 'Yogur natural sin azúcar', 'precio_base' => 3.50, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catLacteos, 'nombre' => 'Queso Fresco', 'descripcion' => 'Queso fresco de vaca', 'precio_base' => 5.99, 'imagen_url' => 'https://via.placeholder.com/200'],
            // Carnes
            ['id_negocio' => $market->id, 'id_categoria' => $catCarnes, 'nombre' => 'Pechuga de Pollo', 'descripcion' => 'Pechuga de pollo deshuesada', 'precio_base' => 7.99, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catCarnes, 'nombre' => 'Carne Molida', 'descripcion' => 'Carne molida fresca de res', 'precio_base' => 8.50, 'imagen_url' => 'https://via.placeholder.com/200'],
            // Bebidas
            ['id_negocio' => $market->id, 'id_categoria' => $catBebidas, 'nombre' => 'Agua Mineral', 'descripcion' => 'Agua mineral 500ml', 'precio_base' => 0.75, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catBebidas, 'nombre' => 'Jugo Natural', 'descripcion' => 'Jugo de naranja natural', 'precio_base' => 2.50, 'imagen_url' => 'https://via.placeholder.com/200'],
            // Abarrotes
            ['id_negocio' => $market->id, 'id_categoria' => $catAbarrotes, 'nombre' => 'Arroz', 'descripcion' => 'Arroz blanco 1kg', 'precio_base' => 1.99, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catAbarrotes, 'nombre' => 'Aceite de Oliva', 'descripcion' => 'Aceite de oliva extra virgen', 'precio_base' => 12.99, 'imagen_url' => 'https://via.placeholder.com/200'],
            ['id_negocio' => $market->id, 'id_categoria' => $catAbarrotes, 'nombre' => 'Pan Integral', 'descripcion' => 'Pan integral fresco del día', 'precio_base' => 1.50, 'imagen_url' => 'https://via.placeholder.com/200'],
        ];

        foreach ($productos as $prod) {
            DB::table('producto')->insert([
                'id' => fake()->uuid(),
                'activo' => true,
                'fecha_creacion' => now(),
                ...$prod
            ]);
        }

        // Datos de prueba para Promociones realistas del Market
        DB::table('promocion')->insert([
            'id' => fake()->uuid(),
            'nombre' => 'Descuento en Frutas',
            'codigo_cupon' => 'FRUTAS20',
            'descripcion' => '20% de descuento en todas las frutas',
            'valor_descuento' => 20,
            'tipo_beneficio' => 'porcentaje',
            'alcance' => 'global',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addDays(30),
            'monto_minimo_compra' => 10,
            'activo' =>true,
            'reglas_extra' => '{}',
            'fecha_creacion' => now(),

        ]);

        DB::table('promocion')->insert([
            'id' => fake()->uuid(),
            'nombre' => 'Compra 2 Llevas 3',
            'codigo_cupon' => 'LLEVAS3',
            'descripcion' => 'En productos de abarrotes: compra 2 y lleva 3',
            'valor_descuento' => 0,
            'tipo_beneficio' => 'porcentaje',
            'alcance' => 'global',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addDays(15),
            'monto_minimo_compra' => 0,
            'activo' => true,
            'reglas_extra' => '{}',
            'fecha_creacion' => now(),
        ]);
    }
}
