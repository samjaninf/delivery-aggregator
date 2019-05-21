<?php

use App\User;
use Carbon\Carbon;
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

$factory->define(App\Availability::class, function (Faker $faker) {
    $date = Carbon::instance($faker->dateTimeBetween('-12 months', '+12 months'));
    $courier = User::where('email', 'courier@prova.it')->firstOrFail();

    if ($faker->boolean) {
        $start = $date->copy()->setTime(12, 00);
        $end = $date->copy()->setTime(14, 30);
    } else {
        $start = $date->copy()->setTime(19, 00);
        $end = $date->copy()->setTime(22, 30);
    }

    return [
        'user_id' => $courier->id,
        'start' => $start,
        'end' => $end,
    ];
});
