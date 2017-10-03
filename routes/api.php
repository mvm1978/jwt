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

Route::post('auth/register', 'UserAuthController@register');
Route::post('auth/login', 'UserAuthController@login');
Route::post('auth/password-reset', 'UserAuthController@passwordReset');
Route::post('auth/password-recovery-by-email', 'UserAuthController@passwordRecoveryByEmail');
Route::get('auth/questions/get', 'QuestionsAuthController@fetch');
Route::get('auth/user-questions/get/{username}', 'UserQuestionsAuthController@fetch');
Route::post('auth/user-question/verify', 'UserQuestionsAuthController@verify');

Route::group(['middleware' => 'jwt.auth'], function() {
    Route::get('user', 'UserAuthController@getAuthUser');
});