<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@funkomacetas.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->call([
            FunkomacetaSeeder::class,
        ]);
    }
}
