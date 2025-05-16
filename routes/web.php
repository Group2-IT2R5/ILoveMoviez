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


$router->get('/test', function () {
    return response()->json(['message' => 'Lumen is working']);
});


$router->get('/test', function () {
    return response()->json(['message' => 'Lumen is working!']);
});

$router->get('/youtube/searchMovie', 'YouTubeController@searchMovie');
$router->get('/movie', 'MovieController@search');
$router->get('/giphy', 'GiphyController@search');
$router->get('/tmdb', 'TMDbController@search');
$router->get('/spotify', 'SpotifyController@search');

