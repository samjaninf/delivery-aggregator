<?php

use Illuminate\Database\Seeder;

class Availabilities extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Availability::class, 200)->create();
    }
}
