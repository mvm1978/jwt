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

        $data = $request->toArray();

        $userID = NULL;

        $newPassword = $data['newPassword'];

        if (isset($data['userID'])) {

            $userID = $data['userID'];

            $token = JWTAuth::attempt([
                'username' => $model->getValue($userID, 'id', 'username'),
                'password' => $data['oldPassword'],
            ]);

            if (! $token) {
                return response()->json([
                    'message' => 'invalid_old_password',
                ], 422);
            }
        }

        if (isset($data['recoveryToken'])) {

            $results = $model->getValue($data['recoveryToken'], 'recovery_token');

            if (! $results) {
                return response()->json([
                    'message' => 'missing_password_recovery_token',
                ], 422);
            } elseif (time() > $results['recovery_token_expire'] + 5 * 60) {
                return response()->json([
                    'message' => 'password_recovery_token_expired',
                ], 422);
            } else {
                $userID = $results['id'];
            }
        }

        try {

            $model->where('id', $userID)
                ->update([
                    'password' => bcrypt($newPassword),
                    'recovery_token' => NULL,
                    'recovery_token_expire' => 0,
                ]);

            return response()->json([
                'message' => 'Password was reset successfully',
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

        $info['token'] = uniqid();
        $info['url'] = $request->url;

        $model->where('email', $email)
            ->update([
                'recovery_token_expire' => time(),
                'recovery_token' => $info['token'],
            ]);

        $model->sendPasswordRecoveryEmail($info);

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