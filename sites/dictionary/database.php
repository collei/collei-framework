<?php

use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Meta\Database;
use Collei\Support\Files\ConfigFile;

/*
 *	database structure
 */

DS::database('laevdic', function(Database $db){

	$conf = ConfigFile::from(grounded('.dbc'));

	$db->parameter('driver', $conf->get('db.driver'));
	$db->parameter('dsn', $conf->get('db.dsn'));
	$db->parameter('username', $conf->get('db.user'));
	$db->parameter('password', $conf->get('db.pass'));
	$db->parameter('database', $conf->get('db.db'));

	$db->table('entries', function(Table $table){
		$table->increments('id');
		$table->string('entry', 40);
		$table->string('origin', 20)->nullable();
		$table->string('origin_from', 40)->nullable();
		$table->integer('anchor_id')->nullable();
		$table->integer('speechpart_id');
	});

	$db->table('meanings', function(Table $table){
		$table->increments('id');
		$table->string('meaning', 40);
		$table->integer('entry_id');
		$table->integer('anchor_id')->nullable();
	});

	$db->table('speechparts', function(Table $table){
		$table->increments('id');
		$table->string('description', 60);
		$table->string('abbreviated', 12);
	});

	$db->connect();

	//$db->migrateAll();

});


/*


CREATE DATABASE laevdic;

CREATE LOGIN usrDictionary WITH PASSWORD = '1979Bratislava'
GO

Use laevdic;
GO

CREATE USER usrDictionary FOR LOGIN usrDictionary

EXEC sp_addrolemember N'db_owner', N'usrDictionary'
GO

create table contacts (
	id integer not null identity(1,1),
	name varchar(60) not null,
	contact_type_id integer null default 0,
	primary key (id));

create table contact_types (
	id integer not null identity(1,1),
	description varchar(60) not null,
	primary key (id));

create table means (
	id integer not null identity(1,1),
	contact_id integer not null default 0,
	mean_type_id integer not null default 0,
	mean varchar(60) null,
	detail varchar(80) null,
	primary key (id));

create table mean_types (
	id integer not null identity(1,1),
	description varchar(60) not null,
	primary key (id));

*/
