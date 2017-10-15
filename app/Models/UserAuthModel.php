<?php

namespace App\Models;

use Mail;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\DB;

use Helpers;

use App\Models\AbstractModel;
use App\Models\UserQuestionsAuthModel;
use App\Models\PasswordRecoveryAuthModel;

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

    public function register($email, $data)
    {
        DB::beginTransaction();

        $user = $this->create([
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
            'email' => $email,
            'first_name' => Helpers::getDefault($data['first_name'], NULL),
            'last_name' => Helpers::getDefault($data['last_name'], NULL),
        ]);

        $userQuestionsModel = new UserQuestionsAuthModel();

        $userQuestionsModel->add($user->id, $data['questions']);

        DB::commit();

        if ($email) {
            $this->sendRegisterEmail($email);
        }
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

    public function passwordReset($userID, $newPassword)
    {
        $passwordRecoveryModel = new PasswordRecoveryAuthModel();

        DB::beginTransaction();

        $this->where('id', $userID)
                ->update([
                    'password' => bcrypt($newPassword),
                ]);

        $passwordRecoveryModel->where('id', $userID)
                ->delete();

        DB::commit();
    }

    /*
    ****************************************************************************
    */

    public function passwordRecoveryByEmail($info)
    {
        $passwordRecoveryModel = new PasswordRecoveryAuthModel();
        // creating an entry in password_recovery table
        $passwordRecoveryModel->create([
            'token' => $info['token'],
            'expire' => time(),
        ]);

        $user = trim($info['first_name'] . ' ' . $info['last_name']);
        $email = $info['email'];
        // sending an email with a link to the user
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

    public function updateUserInfo($info, $userID)
    {
        $this->where('id', $userID)
                ->update([
                    'email' => $info['email'],
                    'first_name' => $info['firstName'],
                    'last_name' => $info['lastName'],
                ]);
    }

    /*
    ****************************************************************************
    */

    public function getUserInfo($token, $data)
    {
        $field = key($data);
        $value = $data[$field];

        $userInfo = $this->getValue($value, $field);

        $id = $userInfo['id'];
        $userneme = $userInfo['username'];
        $email = $userInfo['email'];
        $firstName = $userInfo['first_name'];
        $lastName = $userInfo['last_name'];
        $fullName = $firstName || $lastName ? trim($firstName . ' ' . $lastName) :
            $userneme;

        return compact('token', 'id', 'uername', 'email', 'firstName', 'lastName',
                'fullName');
    }

    /*
    ****************************************************************************
    */

}
