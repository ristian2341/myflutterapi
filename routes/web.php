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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'users'], function () use ($router) {
    $router->post('/registers', ['uses' => 'UserController@register']);
    $router->post('/update_password', ['uses' => 'UserController@updatePassword']);
    $router->post('/login', ['uses' => 'UserController@login']);
    $router->post('/update_profile', ['uses' => 'UserController@profile']);
    $router->post('/reset_password', ['uses' => 'UserController@resetPassword']);
    $router->post('/forgot_password', ['uses' => 'UserController@forgotPassword']);
    $router->get('/get-user/{id}', ['uses' => 'UserController@UserProfile']);
});

$router->group(['prefix' => 'setting'],function() use($router){
    $router->get('/', ['uses' => 'SettingController@setting']);
});

Route::get('/ping', function() {
    try {
        DB::connection()->getPdo(); // cek koneksi db
        return response()->json(['statusCode' => 200,'status' => 'ok']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});