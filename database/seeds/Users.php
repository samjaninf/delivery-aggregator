<?php

use Illuminate\Database\Seeder;
use App\User;

class Users extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Daniele',
                'email' => 'daniele@prova.it',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ],
            [
                'name' => 'Luca',
                'email' => 'luca@prova.it',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
        ];

        User::insert($users);
    }
}
