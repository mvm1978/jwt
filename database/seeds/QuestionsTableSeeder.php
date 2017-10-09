<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\QuestionsAuthModel;

class QuestionsTableSeeder extends Seeder {

    public function run()
    {
        DB::table('questions')->delete();

        QuestionsAuthModel::create([
            'question' => 'What was the model of your first car?'
        ]);

        QuestionsAuthModel::create([
            'question' => 'What is your favourite book?'
        ]);

        QuestionsAuthModel::create([
            'question' => 'What is your favourite movie?'
        ]);

        QuestionsAuthModel::create([
            'question' => 'What is your favourite food?'
        ]);

        QuestionsAuthModel::create([
            'question' => 'What is your favourite beverage?'
        ]);

        QuestionsAuthModel::create([
            'question' => 'What is your nickname at school?'
        ]);

        QuestionsAuthModel::create([
            'question' => 'What city did you meet your spouse?'
        ]);
    }

}