<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Owner;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::factory()->create();
        Owner::factory()->create();


    }
    //php artisan db:seed

}
