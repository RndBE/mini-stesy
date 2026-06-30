<?php

namespace Database\Factories;

use App\Models\t_User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\t_User>
 */
class TUserFactory extends Factory
{
    protected $model = t_User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'level_user' => 'pegawai',
            'instansi_id' => null,
            'status' => 'aktif',
            'suspend_reason' => null,
        ];
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_user' => 'superadmin',
        ]);
    }

    public function instansiAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_user' => 'instansi_admin',
        ]);
    }
}
