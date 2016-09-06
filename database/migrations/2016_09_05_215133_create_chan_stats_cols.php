<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChanStatsCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channel', function ($table) {
            $table->integer('stats_dl')->nullable();
            $table->integer('stats_nodl')->nullable();
            $table->integer('stats_v_pub')->nullable();
            $table->integer('stats_v_unlisted')->nullable();
            $table->integer('stats_v_notfound')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('channel', function ($table) {
          $table->dropColumn('stats_dl');
          $table->dropColumn('stats_nodl');
          $table->dropColumn('stats_v_pub');
          $table->dropColumn('stats_v_unlisted');
          $table->dropColumn('stats_v_notfound');
      });
    }
}
