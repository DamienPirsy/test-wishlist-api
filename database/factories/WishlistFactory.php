<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Wishlist;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

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

$factory->define(Wishlist::class, function(Faker $faker) {
    $name = $faker->sentence(3);
    $alias = Str::of($name)->slug('-');
    return [
        'name' => $name,
        'alias' => $alias
    ];
});