<?php

namespace App\Models;

use App\Models\AbstractModel;

class UserQuestionsAuthModel extends AbstractModel
{
    protected $table = 'user_questions';
    protected $primeKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'question_id',
        'answer',
    ];

    /*
    ****************************************************************************
    */

    public function add($userID, $questions)
    {
        foreach ($questions as $questionID => $answer) {
            $this->create([
                'user_id' => $userID,
                'question_id' => $questionID,
                'answer' => bcrypt($answer),
            ]);
        }
    }

    /*
    ****************************************************************************
    */

}
