<?php

use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Meta\Database;
use Collei\Utils\Files\ConfigFile;

/*
 *	database structure
 */

DS::database('contacta', function(Database $db){

	$conf = ConfigFile::from(grounded('.dbc'));

	$db->parameter('driver', $conf->get('db.driver'));
	$db->parameter('dsn', $conf->get('db.dsn'));
	$db->parameter('username', $conf->get('db.user'));
	$db->parameter('password', $conf->get('db.pass'));
	$db->parameter('database', $conf->get('db.db'));

	$db->table('contacts', function(Table $table){
		$table->increments('id');
		$table->string('name', 60);
		$table->string('avatar', 250);
		$table->integer('contact_type_id');
	});

	$db->table('contact_types', function(Table $table){
		$table->increments('id');
		$table->string('description', 60);
	});

	$db->table('means', function(Table $table){
		$table->increments('id');
		$table->integer('contact_id')->default(0);
		$table->integer('mean_type_id')->default(0);
		$table->string('mean', 60)->nullable();
		$table->string('detail', 80)->nullable();
	});

	$db->table('mean_types', function(Table $table){
		$table->increments('id');
		$table->string('description', 60);
	});

	$db->table('tags', function(Table $table){
		$table->increments('id');
		$table->string('name', 32)->default('');
		$table->string('color', 7)->default('#000000');
	});

	$db->table('contact_tag', function(Table $table){
		$table->increments('id');
		$table->integer('contact_id')->default(0);
		$table->integer('tag_id')->default(0);
	});

	$db->connect();

	//$db->migrateAll();

});


/*



CREATE LOGIN usrContacta WITH PASSWORD = '1979Bratislava'
GO

Use contacta;
GO

CREATE USER usrContacta FOR LOGIN usrContacta

EXEC sp_addrolemember N'db_owner', N'usrContacta'
GO

create table contacts (
	id integer not null identity(1,1),
	name nvarchar(60) not null,
	contact_type_id integer null default 0,
	primary key (id));

create table contact_types (
	id integer not null identity(1,1),
	description nvarchar(60) not null,
	primary key (id));

create table means (
	id integer not null identity(1,1),
	contact_id integer not null default 0,
	mean_type_id integer not null default 0,
	mean nvarchar(60) null,
	detail nvarchar(80) null,
	primary key (id));

create table mean_types (
	id integer not null identity(1,1),
	description nvarchar(60) not null,
	primary key (id));

*/
