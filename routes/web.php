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



// routes/web.php or routes/api.php
$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/movie', 'MovieController@search');  // or whatever method you want to use
    $router->post('/review/{movieTitle}', 'ReviewController@store');
    $router->get('/review', 'ReviewController@index');
    $router->put('/review/{movieTitle}', 'ReviewController@update');
    $router->delete('/review/{movieTitle}', 'ReviewController@destroy');
}); 



