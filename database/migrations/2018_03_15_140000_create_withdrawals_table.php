<?php

// use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class CreateWithdrawalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$query = "CREATE TABLE withdrawals (
					  id             int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
					  ,customer_id   int UNSIGNED  NOT NULL
					  ,amount        decimal(15,2) NOT NULL
					  ,created_at    timestamp     NOT NULL
					  ,updated_at    timestamp     NOT NULL
					  ,FOREIGN KEY (customer_id) REFERENCES customers(id)
					  ,INDEX (customer_id)
                  )
                  DEFAULT CHARACTER SET=utf8
                  COLLATE=utf8_unicode_ci
                  ENGINE=InnoDB";

        DB::statement(DB::raw($query));

		// Schema::create('withdrawals', function(Blueprint $table)
		// {
		// 	$table->increments('id');
		// 	$table->integer('customer_id')->unsigned();
		// 	$table->decimal('amount',15,2);
		// 	$table->timestamps();
			
		// 	$table->engine = 'InnoDB';
		// 	$table->charset = 'utf8';
		// });

		// Schema::table('withdrawals', function (Blueprint $table) {
		// 	$table->foreign('customer_id')->references('id')->on('customers');
		// });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$query = "DROP TABLE withdrawals";

        DB::statement(DB::raw($query));

		// Schema::drop('withdrawals');
	}

}
