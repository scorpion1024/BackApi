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

$router->post('do_login', 'User@do_login');
$router->post('get_userinfo', 'User@get_userinfo');
$router->post('change_admin', 'User@change_admin');
$router->post('do_change', 'User@do_change');
$router->post('do_delete', 'User@do_delete');
$router->post('get_list', 'User@get_list');
$router->get('get_menu', 'User@get_menu');
$router->get('get_weather_data', 'User@get_weather_data');