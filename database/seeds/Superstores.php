<?php

use App\Store;
use Illuminate\Database\Seeder;

class Superstores extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superstore = Store::create([
            'name' => "Mangio sullo Scoglio",
            'code' => 'mangiosulloscoglio',
            'url' => 'https://mangiosulloscoglio.it',
            'consumer_key' => 'ck_123456789',
            'consumer_secret' => 'cs_123456789',
            'is_superstore' => true,
        ]);

        $superstore->stores()->sync([
            1, 2,
        ]);
    }
}
