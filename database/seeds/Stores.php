<?php

use Illuminate\Database\Seeder;
use App\Store;

class Stores extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stores = [
            [
                'name' => "Imburger",
                'code' => 'imburger',
                'url' => 'https://imburger.it',
                'consumer_key' => '***REMOVED***',
                'consumer_secret' => '***REMOVED***'
            ],
            [
                'name' => "Da Scomposto",
                'code' => 'dascomposto',
                'url' => 'http://delivery.dascomposto.it',
                'consumer_key' => '***REMOVED***',
                'consumer_secret' => '***REMOVED***'
            ],
        ];

        Store::insert($stores);
    }
}
