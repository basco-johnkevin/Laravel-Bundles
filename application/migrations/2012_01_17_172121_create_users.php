<?php
/**
 * Create Users
 *
 * A migration for creating the users table.
 *
 * @license     http://www.opensource.org/licenses/mit MIT License
 * @copyright   UserScape, Inc. (http://userscape.com)
 * @author      UserScape Dev Team
 * @link        http://bundles.laravel.com
 * @package     Laravel-Bundles
 * @subpackage  Migrations
 * @filesource
 */
class Create_Users {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function($table)
		{
			$table->create();
			$table->increments('id');
			$table->string('username');
			$table->string('name');
			$table->string('email');
			$table->integer('group_id')->default(2);
			$table->string('ip_address');
			$table->integer('github_uid');
			$table->string('github_token');
			$table->timestamps();
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function($table)
		{
			$table->drop();
		});
	}
}