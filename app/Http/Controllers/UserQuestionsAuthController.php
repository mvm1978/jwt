<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserQuestionsAuthModel;

class UserQuestionsAuthController extends Controller
{
    private $model;

    /*
    ****************************************************************************
    */

    public function __construct(UserQuestionsAuthModel $model)
    {
        $this->model = $model;
    }

    /*
    ****************************************************************************
    */


}