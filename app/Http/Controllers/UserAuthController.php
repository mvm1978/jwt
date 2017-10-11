<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;

use Helpers;

use App\Http\Controllers\AbstractController;

use App\Models\AuthenticationModel;
use App\Models\UserAuthModel;
use App\Models\UserQuestionsAuthModel;
use App\Models\PasswordRecoveryAuthModel;

class UserAuthController extends AbstractController
{
    private $userQuestionsModel;
    private $passwordRecoveryModel;

    /*
    ****************************************************************************
    */

    public function __construct(Request $request)
    {
        $this->construct = parent::__construct($request);

        $this->model = new UserAuthModel();
        $this->userQuestionsModel = new UserQuestionsAuthModel();
        $this->passwordRecoveryModel = new PasswordRecoveryAuthModel();
    }

    /*
    ****************************************************************************
    */

    public function register(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $model = $this->model;

        $data = $request->toArray();

        $user = $email = NULL;

        try {

            $username = $data['username'];
            $email = Helpers::getDefault($data['email'], NULL);

            if ($model->getValue($username, 'username')) {
                return response()->json([
                    'error' => 'username_exists'
                ], 422);
            } elseif ($email && $model->getValue($email, 'email')) {
                return response()->json([
                    'error' => 'email_exists',
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

        } catch (Exception $exception) {
            return response()->json([
                'error' => 'invalid_login_or_password',
            ], 422);
        }

        if ($email) {
            $this->model->sendRegisterEmail($email);
        }

        return response()->json([
            'error' => 'User was created successfully',
        ]);
    }

    /*
    ****************************************************************************
    */

    public function login(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $model = $this->model;
        $id = $email = $firstName = $lastName = $fullName = $token = NULL;

        $username = $request->username;
        $password = $request->password;

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        try {

            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'invalid_login_or_password',
                ], 403);
            }

            $login = $credentials['username'];

            $this->model->updateToken($login, $token);
            $userInfo = $model->getValue($login, 'username');

            $id = $userInfo['id'];
            $email = $userInfo['email'];
            $firstName = $userInfo['first_name'];
            $lastName = $userInfo['last_name'];
            $fullName = $firstName || $lastName ?
                    trim($firstName . ' ' . $lastName) : $login;

        } catch (Exception $exception) {
            return response()->json([
                'error' => 'failed_to_create_token',
            ], 500);
        }

        $return = compact('token', 'id', 'email', 'firstName', 'lastName', 'fullName');

        return response()->json($return);
    }

    /*
    ****************************************************************************
    */

    public function passwordReset(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

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
                    'error' => 'invalid_old_password',
                ], 422);
            }
        }

        if (isset($data['recoveryToken'])) {

            $results = $this->passwordRecoveryModel->getValue($data['recoveryToken'], 'token');

            if (! $results) {
                return response()->json([
                    'error' => 'missing_password_recovery_token',
                ], 422);
            } elseif (time() > $results['expire'] + 5 * 60) {
                return response()->json([
                    'error' => 'password_recovery_token_expired',
                ], 422);
            } else {
                $userID = $results['id'];
            }
        }

        try {

            DB::beginTransaction();

            $model->where('id', $userID)
                    ->update([
                        'password' => bcrypt($newPassword),
                    ]);

            $this->passwordRecoveryModel->where('id', $userID)
                    ->delete();

            DB::commit();

            return response()->json([
                'error' => 'Password was reset successfully',
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => 'error_resetting_password',
            ], 500);
        }
    }

    /*
    ****************************************************************************
    */

    public function passwordRecoveryByEmail(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $model = $this->model;
        $email = $request->email;

        $info = $model->getValue($email, 'email');

        if (! $info) {
            return response()->json([
                'error' => 'email_does_not_exist',
            ], 422);
        }

        $info['token'] = uniqid();
        $info['url'] = $request->url;

        $this->passwordRecoveryModel->create([
            'token' => $info['token'],
            'expire' => time(),
        ]);

        $model->sendPasswordRecoveryEmail($info);

        return response()->json([
            'error' => 'Password recovery completed',
        ]);
    }

    /*
    ****************************************************************************
    */

    public function update(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $body = $request->toArray();

        try {
            $this->model->where('id', $this->userID)
                    ->update([
                        'email' => $body['email'],
                        'first_name' => $body['firstName'],
                        'last_name' => $body['lastName'],
                    ]);
        } catch (Exception $exception) {
            return response()->json([
                'error' => 'error_updating_user_info',
            ], 500);
        }

        return response()->json([
            'error' => 'User Info updated seccessfully',
        ]);
    }

    /*
    ****************************************************************************
    */

    public function getAuthUser(Request $request)
    {
        $authenticationModel = new AuthenticationModel();

        return $authenticationModel->getAuthUser($request->token);
    }

    /*
    ****************************************************************************
    */

}
