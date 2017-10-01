<?php

namespace App\Auth;

use App\Models\UserAuthModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class UserAuthProvider implements UserProvider
{

    public function updateRememberToken(Authenticatable $user, $token)
    {
        return NULL;
    }

    /*
    ****************************************************************************
    */

    public function retrieveByToken($identifier, $token)
    {
        $query = UserAuthModel::where('id', '=', $identifier)
                ->where('token', '=', $token);

        return $query->select(
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    'password'
                )->first();
    }

    /*
    ****************************************************************************
    */

    public function retrieveById($identifier)
    {
        $query = UserAuthModel::where('id', '=', $identifier);

        return $query->select(
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    'password'
                )->first();
    }

    /*
    ****************************************************************************
    */

    public function retrieveByCredentials(array $credentials)
    {
file_put_contents('../../outputs/vadzim.txt', print_r($credentials, TRUE), FILE_APPEND);
        $query = UserAuthModel::where('username', '=', $credentials['username']);

        return $query->select(
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    'password'
                )->first();
    }

    /*
    ****************************************************************************
    */

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $password = $user->getAuthPassword();

        $isValidPassword = password_verify($credentials['password'], $password);

        return $user->username == $credentials['username'] && $isValidPassword;
    }

    /*
    ****************************************************************************
    */

}
