<?php

namespace App\Http\Controllers;

class JWTController extends Controller
{
    protected $model = NULL;

    /*
    ****************************************************************************
    */

    public function __construct($request)
    {
        parent::__construct($request);
    }

    /*
    ****************************************************************************
    */

    public function get($id=NULL)
    {
        $model = $this->model;

        if ($id) {
            $model->where($model->primeKey, $id)
                    ->orderBy($model->primeKey);
        }

        return $model->get();
    }

    /*
    ****************************************************************************
    */

}
