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

    $router->group(['prefix' => 'v1'], function() use ($router) {
        $router->post('register', 'AuthController@register');
        $router->post('login', 'AuthController@login');

        // rotte con autenticazione -->
        $router->group(['middleware' => 'auth'], function() use ($router) {

            // gestione wishlists
            $router->get('wishlists[/{id}]', 'WishlistController@fetch'); // una o tutte le proprie wishlist
            $router->post('wishlists', 'WishlistController@add'); // creo nuova wishlist
            $router->put('wishlists/{id}', 'WishlistController@edit'); // modifico
            $router->delete('wishlists/{id}', 'WishlistController@remove'); // cancello

            $router->get('wishlist/{wid}/products', 'WishlistController@fetchProducts');
            $router->post('wishlist/{wid}/products/{pid}', 'WishlistController@addProducts');
            $router->delete('wishlist/{wid}/products/{pid}', 'WishlistController@removeProducts');

            // gestione products
            $router->get('products[/{id}]', 'ProductController@fetch');
            $router->post('products', 'ProductController@add');
            $router->put('products/{id}', 'ProductController@edit'); // @todo
            $router->delete('products/{id}', 'ProductController@remove');

        });
    });

 });