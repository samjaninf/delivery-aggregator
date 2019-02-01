<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
 */

// === Currently unused ===

$factory->define(App\StatusChange::class, function (Faker $faker) {
    $date = $faker->dateTimeBetween('-6 months', 'now');

    return [
        'user_id' => \App\User::inRandomOrder()->first()->id,
        'store_id' => \App\Store::inRandomOrder()->first()->id,
        'order' => $faker->numberBetween($min = 1000, $max = 9000),
        'status' => 'out-for-delivery',
        'created_at' => $date,
        'updated_at' => $date,
    ];
});
