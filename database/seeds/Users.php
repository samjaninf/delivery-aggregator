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
                'name' => 'Admin',
                'email' => 'admin@prova.it',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@prova.it',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Corriere',
                'email' => 'courier@prova.it',
                'password' => bcrypt('password'),
            ],
        ];

        User::insert($users);

        // Assign roles and stores

        // Admin
        $admin = User::where('email', 'admin@prova.it')->firstOrFail();
        $admin->assign('admin');

        // Manager
        $manager = User::where('email', 'manager@prova.it')->firstOrFail();
        $manager->assign('manager');

        // Courier
        $courier = User::where('email', 'manager@prova.it')->firstOrFail();
        $courier->assign('manager');

        $stores = Store::all();

        foreach ($stores as $store) {
            $admin->stores()->attach($store);
            $manager->stores()->attach($store);
            $courier->stores()->attach($store);
        }
    }
}
