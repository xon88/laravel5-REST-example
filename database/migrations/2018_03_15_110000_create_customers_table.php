<?php

// use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class CreateCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $query = "CREATE TABLE customers (
        			  id                 int UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY
        			 ,first_name         varchar(255)  NOT NULL
        			 ,last_name          varchar(255)  NOT NULL
        			 ,email              varchar(255)  NOT NULL UNIQUE
        			 ,gender             enum('M','F','O','U') NOT NULL
        			 ,country_code       char(2)       NOT NULL
        			 ,bonus_parameter    decimal(5,2)  NOT NULL
        			 ,real_money_balance decimal(15,2) NOT NULL DEFAULT '0'
        			 ,bonus_balance      decimal(15,2) NOT NULL DEFAULT '0'
        			 ,created_at         datetime      NOT NULL
        			 ,updated_at         datetime      NOT NULL
        			 ,version            int           NOT NULL DEFAULT '0'
        			 ,FOREIGN KEY (country_code) REFERENCES countries(id)
                  )
                  DEFAULT CHARACTER SET=utf8
                  COLLATE=utf8_unicode_ci
                  ENGINE=InnoDB";

        DB::statement(DB::raw($query));

		// Schema::create('customers', function(Blueprint $table)
		// {
		// 	$table->increments('id');
		// 	$table->string('first_name');
		// 	$table->string('last_name');
		// 	$table->string('email')->unique();
		// 	$table->enum('gender', ['M','F','O','U']);
		// 	$table->string('country_code',2);
		// 	$table->decimal('bonus_parameter',5,2);
		// 	$table->decimal('real_money_balance',15,2)->default(0);
		// 	$table->decimal('bonus_balance',15,2)->default(0);
		// 	$table->timestamps();
		// 	$table->integer('version')->default(0);

		// 	$table->engine = 'InnoDB';
		// 	$table->charset = 'utf8';
		// });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$query = "DROP TABLE customers";

        DB::statement(DB::raw($query));

		// Schema::drop('customers');
	}

}
