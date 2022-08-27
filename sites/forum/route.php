<?php

use Collei\Http\Routing\Router;

use App\Servlets\ExampleServlet;


Router::get('/home', ExampleServlet::class, 'home')
	->name('site-home');

Router::get('/subject', ExampleServlet::class, 'subject')
	->name('site-subject');

Router::get('/admin/home', ExampleServlet::class, 'admin')
	->name('site-adm-panel');



