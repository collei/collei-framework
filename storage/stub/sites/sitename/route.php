<?php
use Collei\Http\Routing\Router;
use App\Servlets\HomePageServlet;
//
Router::get('/home', HomePageServlet::class, 'home')->name('index-page');
//
