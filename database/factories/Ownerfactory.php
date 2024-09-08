<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\Owner; // Correct the model reference here

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Owner>
 */
class OwnerFactory extends Factory // Capitalize the class name correctly
{
    protected $model = Owner::class; // Reference the correct model

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Crypt::encryptString('Alfred Marcelino'),
            'username' => Crypt::encryptString('alfred45'),
            'email' => Crypt::encryptString('alfredmarcelino@gmail.com'),
            'password' => Hash::make('Alfred45!'),
            'status' => Crypt::encryptString('active'), // Example status value
        ];
    }
}
