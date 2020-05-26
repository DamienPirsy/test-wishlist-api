<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Products;
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

$factory->define(Products::class, function (Faker $faker) {
    $name = $faker->domainWord;
    $alias = Str::of($name)->slug('-');
    return [
        'name' => $name,
        'alias' => $alias,
        'price' => $faker->randomFloat(2, 0, 1000),
        'sku' => $faker->ean8,
        'description' => $faker->paragraph,
    ];
});