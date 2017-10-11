<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

use App\Http\Controllers\AbstractController;

use App\Models\UserQuestionsAuthModel;
use App\Models\UserAuthModel;
use App\Models\PasswordRecoveryAuthModel;

class UserQuestionsAuthController extends AbstractController
{

    /*
    ****************************************************************************
    */

    public function __construct(Request$request)
    {
        parent::__construct($request);

        $this->model = new UserQuestionsAuthModel();
    }

    /*
    ****************************************************************************
    */

    public function fetch($userID)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $result = $this->model->fetch($userID);

        return $result ? $result : response()->json([
            'error' => 'no_recovery_questions_found',
        ], 422);
    }

    /*
    ****************************************************************************
    */

    public function verify(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $data = $request->toArray();

        $result = $this->model->verify($data);

        if (! $result) {
            return response()->json([
                'error' => 'invalid_password_recovery_answer',
            ], 422);
        }

        $usersModel = new UserAuthModel();
        $passwordRecoveryModel = new PasswordRecoveryAuthModel();

        try {

            $userID = $usersModel->getValue($data['username'], 'username', 'id');

            DB::beginTransaction();

            $usersModel->where('username', $data['username'])
                ->update([
                    'password' => bcrypt($data['password']),
                ]);

            $passwordRecoveryModel->where('id', $userID)
                    ->delete();

            DB::commit();

            return response()->json([
                'error' => 'Password recovery completed',
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

    public function update(Request $request)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $body = $request->toArray();

        try {
            DB::beginTransaction();

            $this->model->where('user_id', $this->userID)
                    ->delete();

            $this->model->add($this->userID, $body['questions']);

            DB::commit();
        } catch (Exception $exception) {

            return response()->json([
                'error' => 'error_updating_recovery_questions',
            ], 500);
        }

        return response()->json([
            'error' => 'Recovery Questions updated seccessfully',
        ]);
    }

    /*
    ****************************************************************************
    */

}
