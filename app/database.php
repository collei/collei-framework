<?php

use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Meta\Database;
use Collei\Support\Files\ConfigFile;

use App\Servlets\LogonServlet;

/*
 *	database structure
 */

DS::database('plat', function(Database $db){

	$conf = ConfigFile::from(grounded('../conf/.dbc'));

	$db->parameter('driver', $conf->get('db.driver'));
	$db->parameter('dsn', $conf->get('db.dsn'));
	$db->parameter('username', $conf->get('db.user'));
	$db->parameter('password', $conf->get('db.pass'));
	$db->parameter('database', $conf->get('db.db'));

	$db->table('users', function(Table $table){
		$table->increments('id');
		$table->string('name', 40);
		$table->string('email', 80)->unique();
		$table->timestamp('email_verified_at')->nullable();
		$table->string('password', 255);
		$table->string('remember_token', 128)->nullable();
		$table->boolean('must_change')->default(false);
		$table->boolean('mfa_enabled')->default(false);
		$table->string('mfa_provider', 16)->nullable();
		$table->string('mfa_secret', 255)->nullable();
		$table->timestamps();
	});

	$db->table('roles', function(Table $table){
		$table->increments('id');
		$table->string('name', 40);
		$table->string('color', 6)->default('000000');
		$table->string('icon', 127)->nullable();
		$table->timestamps();
	});

	$db->table('sites', function(Table $table){
		$table->increments('id');
		$table->string('name', 40);
		$table->string('description', 255);
		$table->boolean('is_down')->default(0);
		$table->boolean('is_admin_only')->default(1);
		$table->timestamps();
	});

	$db->table('role_user', function(Table $table){
		$table->increments('id');
		$table->integer('user_id');
		$table->integer('role_id');
		$table->integer('site_id');
	});

	$db->table('visitors', function(Table $table){
		$table->increments('id');
		$table->timestamp('at')->useCurrent();
		$table->string('from_ip', 48)->nullable();
		$table->string('to_uri', 255)->nullable();
		$table->integer('user_id')->defaultValue(0);
	});

	$db->connect();

});

