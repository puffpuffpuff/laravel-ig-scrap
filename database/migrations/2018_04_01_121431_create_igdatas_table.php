<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIgdatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('igdatas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('comment_id');
            $table->string('query_label');
            $table->string('user');
            $table->text('comment');
            $table->string('comment_count');
            $table->string('shortcode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('igdatas');
    }
}
