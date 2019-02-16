<?php

use App\Store;
use Illuminate\Database\Seeder;

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
                'url' => 'https://livorno.imburger.it',
                'consumer_key' => 'ck_123456789',
                'consumer_secret' => 'cs_123456789',
            ],
            [
                'name' => "Da Scomposto",
                'code' => 'dascomposto',
                'url' => 'https://delivery.dascomposto.it',
                'consumer_key' => 'ck_987654321',
                'consumer_secret' => 'cs_987654321',
            ],
        ];

        Store::insert($stores);
    }
}
