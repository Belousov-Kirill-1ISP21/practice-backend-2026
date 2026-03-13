<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate([
            'name' => 'admin',
            'description' => 'Администратор'
        ]);
        
        Role::firstOrCreate([
            'name' => 'user',
            'description' => 'Обычный пользователь'
        ]);
    }
}