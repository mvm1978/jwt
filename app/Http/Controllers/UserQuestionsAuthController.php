<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;

use App\Http\Controllers\AbstractController;

use App\Models\UserQuestionsAuthModel;
use App\Models\UserAuthModel;

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

    public function fetch($username)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $result = $this->model->fetch($username);

        return $result ? $result :
                $this->makeResponse(422, 'no_recovery_questions_found');
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
            return $this->makeResponse(422, 'invalid_password_recovery_answer');
        }

        $usersModel = new UserAuthModel();

        try {

            $userID = $usersModel->getValue($data['username'], 'username', 'id');

            $usersModel->passwordReset($userID, $data['password']);

        } catch (Exception $exception) {
            return $this->makeResponse(500, 'error_resetting_password');
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

            $this->model->updateUserQuestions($this->userID, $body['questions']);
        } catch (Exception $exception) {
            return $this->makeResponse(500, 'error_updating_recovery_questions');
        }

        return $this->makeResponse(200, 'Recovery Questions updated seccessfully');
    }

    /*
    ****************************************************************************
    */

}
