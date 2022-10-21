<?php
use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Meta\Database;
use Collei\Support\Files\ConfigFile;

/*
 *	database structure
 */
DS::database('__DBNAME__', function(Database $db){

	$conf = ConfigFile::from(grounded('.dbc'));

	$db->parameter('driver', $conf->get('db.driver'));
	$db->parameter('dsn', $conf->get('db.dsn'));
	$db->parameter('username', $conf->get('db.user'));
	$db->parameter('password', $conf->get('db.pass'));
	$db->parameter('database', $conf->get('db.db'));

	$db->table('comments', function(Table $table){
		$table->increments('id');
		$table->string('name', 60);
		$table->string('description', 250);
	});

	$db->connect();

});
