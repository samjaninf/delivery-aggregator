<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Store;

class StatusChanges extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      factory(App\StatusChange::class, 100)->create();
    }
}
