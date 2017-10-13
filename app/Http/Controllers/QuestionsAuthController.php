<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\AbstractController;

use App\Models\QuestionsAuthModel;

class QuestionsAuthController extends AbstractController
{

    /*
    ****************************************************************************
    */

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->model = new QuestionsAuthModel();
    }

    /*
    ****************************************************************************
    */

    public function fetch($limit=0)
    {
        if (! empty($this->construct['error'])) {
            return $this->constructErrorResponse();
        }

        $result = $this->model->fetch($limit);

        return $result ? $result :
                $this->makeResponse(422, 'no_recovery_questions_found');
    }

    /*
    ****************************************************************************
    */

}
