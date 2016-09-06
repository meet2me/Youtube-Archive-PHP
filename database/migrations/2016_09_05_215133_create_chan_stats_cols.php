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
      // Credit: https://laracasts.com/discuss/channels/eloquent/sqlite-issue-dropping-columns-sqlitelaravel-providing-incorrect-errors?page=1
      if (Schema::hasColumn('channel', 'stats_dl'))
      {
          Schema::table('channel', function(BLueprint $table)
          {
              $table->dropColumn('stats_dl');
          });
      }

      if (Schema::hasColumn('channel', 'stats_nodl'))
      {
          Schema::table('channel', function(BLueprint $table)
          {
              $table->dropColumn('stats_nodl');
          });
      }

      if (Schema::hasColumn('channel', 'stats_v_pub'))
      {
          Schema::table('channel', function(BLueprint $table)
          {
              $table->dropColumn('stats_v_pub');
          });
      }

      if (Schema::hasColumn('channel', 'stats_v_unlisted'))
      {
          Schema::table('channel', function(BLueprint $table)
          {
              $table->dropColumn('stats_v_unlisted');
          });
      }

      if (Schema::hasColumn('channel', 'stats_v_notfound'))
      {
          Schema::table('channel', function(BLueprint $table)
          {
              $table->dropColumn('stats_v_notfound');
          });
      }
    }
}
