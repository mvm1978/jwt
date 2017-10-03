<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\QuestionsAuthModel;

class QuestionsAuthController extends Controller
{
    private $model;

    /*
    ****************************************************************************
    */

    public function __construct(QuestionsAuthModel $model)
    {
        $this->model = $model;
    }

    /*
    ****************************************************************************
    */

    public function getQuestions($limit=0)
    {
        return $this->model->getQuestions($limit);
    }

    /*
    ****************************************************************************
    */

}