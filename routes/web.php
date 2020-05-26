<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function() use ($router) {
    return $router->app->version();
});

// API route group
$router->group(['prefix' => 'api'], function() use ($router) {

    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    $router->group(['prefix' => 'v1'], function() use ($router) {

        // rotte con autenticazione -->
        $router->group(['middleware' => 'auth'], function() use ($router) {

            // gestione wishlists
            $router->get('wishlists[/{id}]', 'V1\WishlistController@fetch'); // una o tutte le proprie wishlist
            $router->post('wishlists', 'V1\WishlistController@add'); // creo nuova wishlist
            $router->put('wishlists/{id}', 'V1\WishlistController@edit'); // modifico
            $router->delete('wishlists/{id}', 'V1\WishlistController@remove'); // cancello

            $router->get('wishlists/{wid}/products', 'V1\WishlistController@fetchProducts');
            $router->post('wishlists/{wid}/products', 'V1\WishlistController@addProducts');
            $router->delete('wishlists/{wid}/products', 'V1\WishlistController@removeProducts');

            // gestione products
            $router->get('products[/{id}]', 'V1\ProductController@fetch');
            $router->post('products', 'V1\ProductController@add');
            $router->put('products/{id}', 'V1\ProductController@edit'); // @todo
            $router->delete('products/{id}', 'V1\ProductController@remove');

        });
    });

 });