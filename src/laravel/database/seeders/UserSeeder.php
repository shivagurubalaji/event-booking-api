<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'id' => Str::orderedUuid(),
            'name' => 'Shiva',
            'email' => 'shivagurubalaji@gmail.com',
            'password' => Hash::make('Shiva07'),
            'role' => 'admin',
        ]);

        $token = $user->createToken('admin-token')->plainTextToken;

        echo "Sanctum token for {$user->email}: $token\n";
    }
}
