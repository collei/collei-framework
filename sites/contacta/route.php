<?php

use Collei\Http\Routing\Router;
use App\Servlets\ContactBookServlet;
use App\Servlets\ContactAjaxServlet;


Router::get('/desk', ContactBookServlet::class, 'home');
Router::get('/home', ContactBookServlet::class, 'home')->name('contact-home');

Router::get('/search', ContactBookServlet::class, 'search')
	->name('contact-search');

Router::post('/search/results', ContactBookServlet::class, 'doSearch')
	->name('contact-search-do');

// people listing

Router::get('/people/page/{page}', ContactBookServlet::class, 'indexAjax')
	->name('contact-people-ajax');

Router::get('/people', ContactBookServlet::class, 'index')
	->name('contact-people');

Router::post('/people/new', ContactBookServlet::class, 'create')
	->name('contact-add');

Router::post('/people/{id}/edit', ContactBookServlet::class, 'edit')
	->name('contact-modify');

Router::delete('/people/{id}/delete', ContactBookServlet::class, 'delete')
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

// tags

Router::get('/tags', ContactBookServlet::class, 'listTags')
	->name('tag-list');

Router::post('/tags/new', ContactBookServlet::class, 'createTag')
	->name('tag-create');

Router::post('/tags/{tag}/edit', ContactBookServlet::class, 'editTag')
	->name('tag-edit');

Router::delete('/tags/{tag}/delete', ContactBookServlet::class, 'deleteTag')
	->name('tag-delete');

Router::put('/ajax-tags/new', ContactAjaxServlet::class, 'createTag')
	->name('tag-create-ajax');

Router::delete('/ajax-tags/{tag}/delete', ContactAjaxServlet::class, 'deleteTag')
	->name('tag-delete-ajax');



