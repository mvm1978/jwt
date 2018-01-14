<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'Cors'], function() {

    Route::post('auth/users', 'UserAuthController@register');
    Route::put('auth/users', 'UserAuthController@update');
    Route::post('auth/users/login', 'UserAuthController@login');
    Route::get('auth/users/token', 'UserAuthController@checkToken');
    Route::patch('auth/users/password', 'UserAuthController@passwordRecoveryByEmail');
    Route::put('auth/users/password', 'UserAuthController@passwordReset');

    Route::get('auth/questions', 'QuestionsAuthController@fetch');

    Route::get('auth/user-questions/{username}', 'UserQuestionsAuthController@fetch');
    Route::put('auth/user-questions', 'UserQuestionsAuthController@update');
    Route::patch('auth/user-questions/{username}', 'UserQuestionsAuthController@verify');
});

Route::group(['middleware' => 'jwt.auth', 'middleware' => 'Cors'], function() {
    Route::get('user', 'UserAuthController@getAuthUser');
});