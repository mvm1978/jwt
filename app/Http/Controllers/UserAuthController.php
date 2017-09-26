<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\UserAuthModel;
use JWTAuthException;

class UserAuthController extends Controller
{
    private $model;

    /*
    ****************************************************************************
    */

    public function __construct(UserAuthModel $model)
    {
        $this->model = $model;
    }

    /*
    ****************************************************************************
    */

    public function register(Request $request)
    {
        $user = $this->model->create([
            'username' => $request->get('username'),
            'password' => bcrypt($request->get('password'))
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User account created successfully',
            'data' => $user,
        ]);
    }

    /*
    ****************************************************************************
    */

    public function login(Request $request)
    {
        $credentials = [];
        $id = $token = NULL;

        parse_str($request->getContent(), $credentials);

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['invalid_login_or_password'], 422);
            }

            $login = $credentials['username'];

            $this->model->updateToken($login, $token);
            $id = $this->model->getIDBylogin($login);

        } catch (JWTAuthException $exception) {
            return response()->json(['failed_to_create_token'], 500);
        }

        return response()->json(compact('token', 'id'));
    }

    /*
    ****************************************************************************
    */

    public function getAuthUser(Request $request)
    {
        $data = JWTAuth::toUser($request->token);

        $attributes = $data->getAttributes();

        return md5($request->token) == $attributes['session_token'] ?
                response()->json([
                    'data' => $data
                ]) : response()->json(['invalid_token'], 400);
    }

    /*
    ****************************************************************************
    */

}