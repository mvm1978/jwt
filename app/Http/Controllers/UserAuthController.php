<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Exception;

use Helpers;

use App\Http\Controllers\AbstractController;

use App\Models\AuthenticationModel;
use App\Models\UserAuthModel;
use App\Models\PasswordRecoveryAuthModel;

class UserAuthController extends AbstractController
{

    /*
    ****************************************************************************
    */

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->model = new UserAuthModel();
    }

    /*
    ****************************************************************************
    */

    public function login(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $passwordRecoveryModel = new PasswordRecoveryAuthModel();

        $username = $request->username;
        $password = $request->password;

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        try {

            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->makeResponse(403, 'invalid_login_or_password');
            }

            $login = $credentials['username'];

            $this->model->updateToken($login, $token);

            $return = $this->model->getUserInfo($token, [
                'username' => $login
            ]);

            $passwordRecoveryModel->where('id', $return['id'])
                    ->delete();

        } catch (Exception $exception) {
            return $this->makeResponse(500, 'failed_to_create_token');
        }

        return response()->json($return);
    }

    /*
    ****************************************************************************
    */

    public function register(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $data = $request->toArray();

        $email = Helpers::getDefault($data['email'], NULL);

        if ($this->model->getValue($data['username'], 'username')) {
            return $this->makeResponse(422, 'username_exists');
        } elseif ($email && $this->model->getValue($email, 'email')) {
            return $this->makeResponse(422, 'email_exists');
        }

        try {
            $this->model->register($email, $data);
        } catch (Exception $exception) {
            return $this->makeResponse(403, 'invalid_login_or_password');
        }

        return $this->makeResponse(200, 'User was created successfully');
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

        if (isset($data['userID'])) {

            $userID = $data['userID'];

            $token = JWTAuth::attempt([
                'username' => $model->getValue($userID, 'id', 'username'),
                'password' => $data['oldPassword'],
            ]);

            if (! $token) {
                return $this->makeResponse(403, 'invalid_old_password');
            }
        }

        $passwordRecoveryModel = new PasswordRecoveryAuthModel();

        if (isset($data['recoveryToken'])) {

            $results = $passwordRecoveryModel->getValue($data['recoveryToken'], 'token');

            if (! $results) {
                return $this->makeResponse(422, 'missing_password_recovery_token');
            } elseif (time() > $results['expire'] + env('PASSWORD_RECOVERY_LINK_EXPIRE') * 1000) {
                return $this->makeResponse(403, 'password_recovery_token_expired');
            } else {
                $userID = $results['id'];
            }
        }

        if (! $userID) {
            return $this->makeResponse(422, 'failed_to_get_user');
        }

        try {
            $model->passwordReset($userID, $data['newPassword']);
        } catch (Exception $exception) {
            return $this->makeResponse(500, 'error_resetting_password');
        }

        return $this->makeResponse(200, 'Password was reset successfully');
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

        $info = $model->getValue($request->email, 'email');

        if (! $info) {
            return $this->makeResponse(403, 'email_does_not_exist');
        }

        $info['token'] = uniqid();
        $info['url'] = $request->url;

        try {
            $model->passwordRecoveryByEmail($info);
        } catch (Exception $exception) {
            return $this->makeResponse(500, 'error_recovering_password');
        }

        return $this->makeResponse(200, 'Password recovery completed');
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

            $this->model->updateUserInfo($body, $this->userID);

            $return = $this->model->getUserInfo($this->token, [
                'id' => $this->userID,
            ]);

        } catch (Exception $exception) {
            return $this->makeResponse(500, 'error_updating_user_info');
        }

        return $return;
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

    public function checkToken()
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        return $this->makeResponse(200, 'Token verified');
    }

    /*
    ****************************************************************************
    */

}
