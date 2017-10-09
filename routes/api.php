<?php

use Illuminate\Http\Request;

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

Route::post('auth/users/register', 'UserAuthController@register');
Route::post('auth/users/login', 'UserAuthController@login');
Route::put('auth/users/password-reset', 'UserAuthController@passwordReset');
Route::put('auth/users/update', 'UserAuthController@update');
Route::post('auth/users/password-recovery-by-email', 'UserAuthController@passwordRecoveryByEmail');
Route::get('auth/questions/get', 'QuestionsAuthController@fetch');
Route::get('auth/user-questions/get/{username}', 'UserQuestionsAuthController@fetch');
Route::post('auth/user-questions/verify', 'UserQuestionsAuthController@verify');
Route::put('auth/user-questions/update', 'UserQuestionsAuthController@update');

Route::group(['middleware' => 'jwt.auth'], function() {
    Route::get('user', 'UserAuthController@getAuthUser');
});