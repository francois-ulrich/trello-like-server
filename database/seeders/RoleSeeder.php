<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create( ['name' => 'User', 'slug' => 'user'], );
        Role::create( ['name' => 'Admin', 'slug' => 'admin'], );

        $userRole = Role::where('slug', 'user')->first()->id;
        User::whereNull('role_id') ->update(['role_id' => $userRole]);
    }
}
