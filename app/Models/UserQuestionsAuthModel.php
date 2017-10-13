<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    public function fetch($username)
    {
        $result = $this
                ->select(
                    'question_id',
                    'question'
                )
                ->join('users', 'users.id', '=', 'user_questions.user_id')
                ->join('questions', 'questions.id', '=', 'user_questions.question_id')
                ->where('username', $username)
                ->inRandomOrder()
                ->first();

        return $result ? $result->toArray() : [];
    }

    /*
    ****************************************************************************
    */

    public function verify($data)
    {
        $result = $this
                ->select('answer')
                ->join('users', 'users.id', '=', 'user_questions.user_id')
                ->join('questions', 'questions.id', '=', 'user_questions.question_id')
                ->where('username', $data['username'])
                ->where('question_id', $data['questionID'])
                ->inRandomOrder()
                ->first();

        if (! $result) {
            return FALSE;
        }

        $info = $result->toArray();

        return Hash::check($data['answer'], $info['answer']);
    }

    /*
    ****************************************************************************
    */

    public function updateUserQuestions($userID, $questions)
    {
        DB::beginTransaction();

        $this->where('user_id', $userID)
                ->delete();

        $this->add($userID, $questions);

        DB::commit();
    }

    /*
    ****************************************************************************
    */

}
