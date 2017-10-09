<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\UserQuestionsAuthModel;
use App\Models\UserAuthModel;
use App\Models\PasswordRecoveryAuthModel;

class UserQuestionsAuthController extends Controller
{
    private $model;
    private $userModel;

    /*
    ****************************************************************************
    */

    public function __construct(UserQuestionsAuthModel $model)
    {
        $this->model = $model;
        $this->userModel = new UserAuthModel();
        $this->passwordRecoveryModel = new PasswordRecoveryAuthModel();
    }

    /*
    ****************************************************************************
    */

    public function fetch($userID)
    {
        $result = $this->model->fetch($userID);

        return $result ? $result : response()->json([
            'message' => 'no_recovery_questions_found',
        ], 422);
    }

    /*
    ****************************************************************************
    */

    public function verify(Request $request)
    {
        $data = $request->toArray();

        $result = $this->model->verify($data);

        if (! $result) {
            return response()->json([
                'message' => 'invalid_password_recovery_answer',
            ], 422);
        }

        try {

            $userID = $this->userModel->getValue($data['username'], 'username', 'id');

            DB::beginTransaction();

            $this->userModel->where('username', $data['username'])
                ->update([
                    'password' => bcrypt($data['password']),
                ]);

            $this->passwordRecoveryModel->where('id', $userID)
                    ->delete();

            DB::commit();

            return response()->json([
                'message' => 'Password recovery completed',
            ]);

        } catch (JWTAuthException $exception) {
            return response()->json([
                'message' => 'error_resetting_passworв',
            ], 500);
        }
    }

    /*
    ****************************************************************************
    */

}
