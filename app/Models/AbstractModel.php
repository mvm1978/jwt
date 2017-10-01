<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbstractModel extends Model
{
    protected $primeKey = NULL;
    protected $table = NULL;
    public $timestamps = FALSE;

    /*
    ****************************************************************************
    */

    public function getValue($value, $queryField=NULL, $returnField=NULL)
    {
        $whereField = $queryField ? $queryField : $this->primeKey;

        $results = $this->where($whereField, $value)->first();

        $values = $results ? $results->toArray() : [];

        if ($returnField) {
            return $values ? $values[$returnField] : NULL;
        } else {
            return $values;
        }
    }

    /*
    ****************************************************************************
    */
}
