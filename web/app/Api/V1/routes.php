<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => 'sumrameet',
    'namespace' => '\App\Api\V1\Controllers',
    'middleware' => 'checkUser'
], function ($router) {
    /**
     * Test
     */
    $router->get('/test', 'TestController@test');
    /**
     * User profile
     */
    $router->get('/profile', 'ProfileController@index');
    $router->post('/profile', 'ProfileController@store');
    $router->post('/profile/invite', 'ProfileController@invite');

    /**
     * Meetings / Room
     */
    $router->get('/meetings', 'RoomController@index');
    $router->get('/meetings/{id}', 'RoomController@show');
    $router->post('/meetings', 'RoomController@store');
    $router->put('/meetings/{id}', 'RoomController@update');
    $router->delete('/meetings/{id}', 'RoomController@destroy');

    /**
     * Session / History
     */
    $router->get('/sess-users', 'SessionController@index');
    $router->get('/sess-enter', 'SessionController@store');
    $router->post('/sess-exit', 'SessionController@exitRoom');

    /**
     * Calendar
     */
    $router->get('/calendars', 'CalendarController@index');
    $router->post('/calendars', 'CalendarController@store');
    $router->get('/calendars/{id}', 'CalendarController@show');
    $router->put('/calendars/{id}', 'CalendarController@update');
    $router->delete('/calendars/{id}', 'CalendarController@destroy');

    /**
     * ADMIN PANEL
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => 'checkAdmin'
    ], function ($router) {

        // history meeting
        $router->post('/sessions', 'HistoryRoomController@store');

        // history user
        $router->post('/users', 'HistoryUserController@store');
        $router->put('/users/{id}', 'HistoryUserController@update');
    });
});
