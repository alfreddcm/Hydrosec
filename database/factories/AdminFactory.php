<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\Admin;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Crypt::encryptString('Admin'),
            'username' => Crypt::encryptString('admin'),
            'email' => Crypt::encryptString('hydrosec1@gmail.com'),
            'password' => Hash::make('admin'),
            'status' => Crypt::encryptString('1'),
        ];
    }
}
