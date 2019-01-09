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
            ],
            [
                'name' => 'Luca',
                'email' => 'luca@prova.it',
                'password' => bcrypt('password'),
            ],
        ];

        User::insert($users);

        // Assign roles and stores

        // User 1
        $user = User::where('email', 'daniele@prova.it')->firstOrFail();
        $user->assign('admin');

        $stores = Store::all();

        foreach ($stores as $store) {
            $user->stores()->attach($store);
        }

        // User 2
        $user2 = User::where('email', 'luca@prova.it')->firstOrFail();
        $user2->assign('manager');
    }
}
