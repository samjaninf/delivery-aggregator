<?php

use App\Store;
use App\User;
use Illuminate\Database\Seeder;

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
                'role' => User::ADMIN,
            ],
            [
                'name' => 'Luca',
                'email' => 'luca@prova.it',
                'password' => bcrypt('password'),
                'role' => User::USER,
            ],
        ];

        User::insert($users);

        $user = User::find(1);
        $stores = Store::all();

        foreach ($stores as $store) {
            $user->stores()->attach($store);
        }
    }
}
