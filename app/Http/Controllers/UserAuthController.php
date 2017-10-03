<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\UserAuthModel;
use App\Models\UserQuestionsAuthModel;
use JWTAuthException;
use Helpers;

class UserAuthController extends Controller
{
    private $model;
    private $questionsModel;
    private $userQuestionsModel;

    /*
    ****************************************************************************
    */

    public function __construct(UserAuthModel $model)
    {
        $this->model = $model;
        $this->userQuestionsModel = new UserQuestionsAuthModel();
    }

    /*
    ****************************************************************************
    */

    public function register(Request $request)
    {
        $model = $this->model;

        $data = $request->toArray();

        $user = $email = NULL;

        try {

            $username = $data['username'];
            $email = Helpers::getDefault($data['email']);

            if ($model->getValue($username, 'username')) {
                return response()->json([
                    'message' => 'username_exists'
                ], 422);
            } elseif ($email && $model->getValue($email, 'email')) {
                return response()->json([
                    'message' => 'email_exists',
                ], 422);
            }

            DB::beginTransaction();

            $user = $model->create([
                'username' => $username,
                'password' => bcrypt($data['password']),
                'email' => $email,
                'first_name' => Helpers::getDefault($data['first_name'], NULL),
                'last_name' => Helpers::getDefault($data['last_name'], NULL),
            ]);

            $this->userQuestionsModel->add($user->id, $data['questions']);

            DB::commit();

        } catch (JWTAuthException $exception) {
            return response()->json([
                'message' => 'invalid_login_or_password',
            ], 422);
        }

        if ($email) {
            $this->model->sendRegisterEmail($email);
        }

        return response()->json([
            'message' => 'User was created successfully',
        ]);
    }

    /*
    ****************************************************************************
    */

    public function login(Request $request)
    {
        $model = $this->model;
        $id = $token = NULL;

        $username = $request->username;
        $password = $request->password;

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        try {

            $expire = $model->getValue($username, 'username', 'password_expire');

            if ($expire && time() - $expire > 5 * 60) {
                return response()->json([
                    'message' => 'password_expired',
                ], 403);
            }

            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'invalid_login_or_password',
                ], 403);
            }

            $login = $credentials['username'];

            $this->model->updateToken($login, $token);
            $id = $model->getValue($login, 'username', 'id');

        } catch (JWTAuthException $exception) {
            return response()->json([
                'message' => 'failed_to_create_token',
            ], 500);
        }

        return response()->json(compact('token', 'id'));
    }

    /*
    ****************************************************************************
    */

    public function passwordReset(Request $request)
    {
        $model = $this->model;

        $userID = $request->userID;
        $oldPassword = $request->oldPassword;
        $newPassword = $request->newPassword;

        $token = JWTAuth::attempt([
            'username' => $model->getValue($userID, 'id', 'username'),
            'password' => $oldPassword,
        ]);

        if (! $token) {
            return response()->json([
                'message' => 'invalid_old_password',
            ], 403);
        }

        try {

            $model->where('id', $userID)
                ->update([
                    'password' => bcrypt($newPassword),
                    'password_expire' => 0,
                ]);

            return response()->json([
                'message' => 'User account created successfully',
            ]);

        } catch (JWTAuthException $exception) {
            return response()->json([
                'message' => 'error_resetting_password',
            ], 500);
        }
    }

    /*
    ****************************************************************************
    */

    public function passwordRecoveryByEmail(Request $request)
    {
        $model = $this->model;
        $email = $request->email;

        $info = $model->getValue($email, 'email');

        if (! $info) {
            return response()->json([
                'message' => 'email_does_not_exist',
            ], 422);
        }

        $password = uniqid();

        $model->where('email', $email)
            ->update([
                'password' => bcrypt($password),
                'password_expire' => time(),
            ]);

        $model->sendPasswordRecoveryEmail($info, $password);

        return response()->json([
            'message' => 'Password recovery completed',
        ]);
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
                ]) : response()->json([
                    'message' => 'invalid_token',
                ], 403);
    }

    /*
    ****************************************************************************
    */

}