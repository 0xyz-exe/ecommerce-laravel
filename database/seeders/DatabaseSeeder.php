<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProductSeeder::class);
        if (app()->environment('local')) {
            User::firstOrCreate(['email' => 'admin@example.test'], [
                'name' => 'Local Store Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('ChangeMe123!'),
                'is_admin' => true,
            ]);
        }
    }
}
