<?php

use App\Models\CustomerNote;
use Faker\Generator as Faker;

$factory->define(CustomerNote::class, function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'note' => $faker->text(200),
    ];
});
