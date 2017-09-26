<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class UserAuthModel extends Model implements AuthenticatableContract
{
    use Authenticatable;

    public $timestamps = FALSE;
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'session_token',
    ];

    /*
    ****************************************************************************
    */

    public function updateToken($login, $token)
    {
        $encrypted = md5($token);

        $this->where('username', $login)
            ->update([
                'session_token' => $encrypted
            ]);
    }

    /*
    ****************************************************************************
    */

    public function getIDBylogin($login)
    {
        $result = $this->select('id')
            ->where('username', $login)
            ->first();

        return $result['id'];
    }

    /*
    ****************************************************************************
    */

}
