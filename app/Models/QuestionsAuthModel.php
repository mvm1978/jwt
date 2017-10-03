<?php

namespace App\Models;

use App\Models\AbstractModel;

class QuestionsAuthModel extends AbstractModel
{
    protected $table = 'questions';
    protected $primeKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question',
    ];

    /*
    ****************************************************************************
    */

    public function fetch($limit=0)
    {
        $query = $limit ? $this->orderByRaw('RAND()')->limit($limit) : $this;

        return $query->get()->toArray();
    }

    /*
    ****************************************************************************
    */

}
