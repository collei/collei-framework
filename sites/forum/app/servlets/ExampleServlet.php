<?php

namespace App\Servlets;

use Collei\Http\HttpServlet;
use Collei\Http\Request;
use Collei\Http\Response;

class ExampleServlet extends HttpServlet
{
	public function home(Request $request)
	{
		return view('index');
	}

	public function subject(Request $request)
	{
		return view('index');
	}

	public function admin(Request $request)
	{
		return view('index');
	}
}

