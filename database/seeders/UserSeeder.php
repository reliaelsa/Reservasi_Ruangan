<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admins =[
            [
                'name' => 'Admin 1',
                'email' => 'admin1@example.com',
                'password' => Hash::make('password12345'),
            ],
            [
                'name' => 'Admin 2',
                'email' => 'admin2@example.com',
                'password' => Hash::make('password12345'),
            ],
            [
                'name' => 'Admin 3',
                'email' => 'admin3@example.com',
                'password' => Hash::make('password12345'),
            ],
            [
                'name' => 'Admin 4',
                'email' => 'admin4@example.com',
                'password' => Hash::make('password12345'),
            ],
            [
                'name' => 'Admin 5',
                'email' => 'admin5@example.com',
                'password' => Hash::make('password12345'),
            ],

        ];

        foreach($admins as $adminData){
            $admin = User::create($adminData);
            $admin->assignRole('admin');
            $admin->role = 'admin';
            $admin->save();
        }
        // // assign role admin (spatie)
        // $admin->assignRole('admin');

        // // simpan juga di kolom users.role supaya sinkron
        // $admin->role = 'admin';
        // $admin->save();
    }
}
