<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        
        User::create([
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
            'last_name' => 'Администратор',
            'first_name' => 'Главный',
            'phone' => '+79991112233'
        ]);
        
        User::create([
            'email' => 'user@example.com',
            'password_hash' => Hash::make('user123'),
            'role_id' => $userRole->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'phone' => '+79991112244'
        ]);
    }
}