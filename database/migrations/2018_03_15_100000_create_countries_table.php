<?php

// use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = "CREATE TABLE countries (
                      id   char(2)      NOT NULL PRIMARY KEY
                     ,name varchar(255) NOT NULL
                  )
                  DEFAULT CHARACTER SET=utf8
                  COLLATE=utf8_unicode_ci
                  ENGINE=InnoDB";

        DB::statement(DB::raw($query));

        // Schema::create('countries', function (Blueprint $table) {                
        //     $table->char('id',2)->primary();
        //     $table->string('name');

        //     $table->engine = 'InnoDB';
        //     $table->charset = 'utf8';
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $query = "DROP TABLE countries";

        DB::statement(DB::raw($query));

        // Schema::drop('countries');
    }
}
