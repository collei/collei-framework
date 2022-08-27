<?php

use Collei\Http\Routing\Router;
use App\Servlets\DictionaryKeeperServlet;


Router::get('/home', DictionaryKeeperServlet::class, 'home')
	->name('dic-home');

Router::get('/entries/list', DictionaryKeeperServlet::class, 'list')
	->name('dic-list');

Router::get('/entries/import', DictionaryKeeperServlet::class, 'import')
	->name('dic-import');

Router::get('/entries/{id}/json', DictionaryKeeperServlet::class, 'entry')
	->name('dic-entry-json');

Router::get('/entries/export', DictionaryKeeperServlet::class, 'export')
	->name('dic-export');

Router::post('/entries/{id}/update', DictionaryKeeperServlet::class, 'updateIt')
	->name('dic-update-entry');

Router::get('/search', DictionaryKeeperServlet::class, 'search')
	->name('dic-search');

Router::post('/search/results', DictionaryKeeperServlet::class, 'searchResults')
	->name('dic-search-results');

// entry admin

Router::get('/entries/new', DictionaryKeeperServlet::class, 'newEntryStart')
	->name('contact-people');

Router::post('/entries/create', DictionaryKeeperServlet::class, 'newEntry')
	->name('contact-add');

/*
Router::post('/people/{id}/edit', ContactBookServlet::class, 'edit')
	->name('contact-modify');

Router::post('/people/{id}/delete', ContactBookServlet::class, 'delete')
	->name('contact-remove');

// means of contact

Router::get('/people/{id}/means', ContactBookServlet::class, 'detail')
	->name('contact-means');

Router::post('/people/{contact_id}/mean/new', ContactBookServlet::class, 'createMean')
	->name('contact-mean-add');

Router::post('/people/{contact_id}/mean/{mean_id}/edit', ContactBookServlet::class, 'editMean')
	->name('contact-mean-modify');

Router::post('/people/{contact_id}/mean/{mean_id}/delete', ContactBookServlet::class, 'deleteMean')
	->name('contact-mean-remove');

*/




