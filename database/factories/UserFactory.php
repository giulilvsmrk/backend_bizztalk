<?php
declare(strict_types=1);
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class UserFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lat = fake()->latitude(-16.5, -16.4);
        $lng = fake()->longitude(-68.2, -68.1);

        return [
            'id' => fake()->uuid(),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'correo' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('password'),
            'telefono' => fake()->unique()->phoneNumber(),
            'ubicacion_actual' => DB::raw("point($lat, $lng)"),
            'activo' => true,
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ];
    }

    public function eliminado(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
            'fecha_eliminacion' => now(),
        ]);
    }
}
