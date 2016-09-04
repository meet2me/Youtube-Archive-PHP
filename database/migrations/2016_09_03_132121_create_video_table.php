<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('Chan_ID');
            $table->string('YT_ID');
            $table->string('Title')->nullable();
            $table->longText('Description')->nullable();
            $table->string('YT_Status')->nullable();
            $table->string('File_Name')->nullable();
            $table->string('File_Status')->nullable();
            $table->dateTime('Upload_Date')->nullable();
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
        Schema::drop('video');
    }
}
