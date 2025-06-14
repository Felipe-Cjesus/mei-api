<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!User::where('email', 'Felipe@meiapp.com')->first()) {
            User::create([
                'name' => 'Felipe',
                'email' => 'felipe@meiapp.com',
                'password' => Hash::make('123456a', ['rounds' => 12]),
            ]);
        }
        if(!User::where('email', 'Gabriele@meiapp.com')->first()) {
            User::create([
                'name' => 'Gabriele',
                'email' => 'gabriele@meiapp.com',
                'password' => Hash::make('123456a', ['rounds' => 12]),
            ]);
        }
        if(!User::where('email', 'Charlie@meiapp.com')->first()) {
            User::create([
                'name' => 'Charlie',
                'email' => 'charlie@meiapp.com',
                'password' => Hash::make('123456a', ['rounds' => 12]),
            ]);
        }
    }
}
