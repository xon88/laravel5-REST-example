<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        // Disable Foreign key check for this connection before running seeders
        //this will allow us to use truncate to reset auto-increment counters - http://stackoverflow.com/questions/20546253/how-to-reset-auto-increment-in-laravel-user-deletion
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call('InitDatabaseSeeder');

        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
