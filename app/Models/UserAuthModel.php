<?php

namespace App\Models;

use Mail;
use Illuminate\Auth\Authenticatable;

use App\Models\AbstractModel;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class UserAuthModel extends AbstractModel implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'users';
    protected $primeKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'email',
        'first_name',
        'last_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'session_token',
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

    public function sendRegisterEmail($email)
    {
        $result = $this->select(
                'first_name',
                'last_name'
            )
            ->where('email', $email)
            ->first();

        $data = $result->toArray();

        $user = trim($data['first_name'] . ' ' . $data['last_name']);

        Mail::send('emails.register', ['user' => $user], function ($mail) use ($email, $user) {

            $from = env('MAIL_USERNAME');

            $mail->from($from, 'MVM Sing Up')
                    ->to($email, $user)
                    ->subject('Registering to MVM');
        });
    }

    /*
    ****************************************************************************
    */

    public function sendPasswordRecoveryEmail($info)
    {
        $user = trim($info['first_name'] . ' ' . $info['last_name']);
        $email = $info['email'];

        Mail::send('emails.passwordRecovery', ['info' => $info], function ($mail) use ($email, $user) {

            $from = env('MAIL_USERNAME');

            $mail->from($from, 'MVM Password Recovery')
                    ->to($email, $user)
                    ->subject('Password Recovert at MVM');
        });
    }

    /*
    ****************************************************************************
    */

}
