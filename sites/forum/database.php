<?php

use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Meta\Database;
use Collei\Support\Files\ConfigFile;

/*
 *	database structure
 */

DS::database('example', function(Database $db){

	$conf = ConfigFile::from(grounded('.dbc'));

	$db->parameter('driver', $conf->get('db.driver'));
	$db->parameter('dsn', $conf->get('db.dsn'));
	$db->parameter('username', $conf->get('db.user'));
	$db->parameter('password', $conf->get('db.pass'));
	$db->parameter('database', $conf->get('db.db'));

	$db->table('profiles', function(Table $table){
		$table->increments('id');
		$table->string('name', 60);
		$table->string('email', 80);
		$table->string('title', 80);
		$table->string('profile', 255);
		$table->integer('level_id');
	})->migrate();

	$db->table('profile_levels', function(Table $table){
		$table->increments('id');
		$table->string('name', 40);
		$table->string('description', 127);
	})->migrate();

	$db->table('posts', function(Table $table){
		$table->increments('id');
		$table->string('title', 127)->nullable();
		$table->string('content', 4000)->nullable();
		$table->integer('forum_id')->default(0);
		$table->integer('author_id')->default(0);
	})->migrate();

	$db->table('categories', function(Table $table){
		$table->increments('id');
		$table->string('title', 127)->nullable();
		$table->string('description', 512)->nullable();
	})->migrate();

	$db->table('forums', function(Table $table){
		$table->increments('id');
		$table->string('title', 127)->nullable();
		$table->string('description', 512)->nullable();
		$table->integer('category_id')->default(0);
		$table->integer('parent_id')->default(0);
	})->migrate();

	$db->table('permissions', function(Table $table){
		$table->increments('id');
		$table->string('description', 60);
		$table->integer('level_id')->default(0);
		$table->integer('category_id')->default(0);
		$table->integer('forum_id')->default(0);
		$table->integer('read')->default(1);
		$table->integer('write')->default(0);
		$table->integer('modify')->default(0);
		$table->integer('delete')->default(0);
	})->migrate();

	$db->connect();

});


/*



CREATE LOGIN usrForum WITH PASSWORD = '1979Foro'
GO

Use forum;
GO

CREATE USER usrForum FOR LOGIN usrForum

EXEC sp_addrolemember N'db_owner', N'usrForum'
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
