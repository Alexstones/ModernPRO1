<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $values = [
            'username' => 'admin',
            'password' => Hash::make('admin'),
        ];

        if (Schema::hasColumn('users', 'email')) {
            $values['email'] = 'admin@example.com';
        }

        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            $values
        );
    }
}
