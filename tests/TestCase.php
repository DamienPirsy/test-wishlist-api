<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{

    protected $user;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Ovverride per passare il token alle chiamate, quando serve
     *
     * @param [type] $method
     * @param [type] $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param [type] $content
     * @return void
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        if ($this->user) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . JWTAuth::fromUser($this->user);
        }

        $server['HTTP_ACCEPT'] = 'application/json';
        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function asLoggedUser($user = null)
    {
        if (!$user) {
            $user = $this->makeUser();
        }
        return $this->actingAs($user);
    }

    /**
     * Genero un utente
     *
     * @return User
     */
    protected function makeUser() 
    {
        return factory('App\User')->create([
            'password' => Hash::make(/*Str::random(10)*/ 123456)
        ]);
    }
}
