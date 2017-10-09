<?php

namespace App\Models;

use App\Models\AbstractModel;

class PasswordRecoveryAuthModel extends AbstractModel
{
    protected $table = 'password_recovery';
    protected $primeKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'expire',
    ];

    /*
    ****************************************************************************
    */

    public function fetch()
    {
        return $this->get()->toArray();
    }

    /*
    ****************************************************************************
    */

}
