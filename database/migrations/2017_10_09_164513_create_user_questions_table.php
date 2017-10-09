<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_questions', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('question_id')->length(5)->unsigned();
            $table->string('answer', 100);

            $table->unique([
                'question_id',
                'user_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_questions');
    }
}
