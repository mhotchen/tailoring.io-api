<?php

use App\Models\User;
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

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('password'),
        'status' => User::STATUS_ACTIVE,
    ];
});
