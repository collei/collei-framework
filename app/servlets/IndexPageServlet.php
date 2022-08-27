<?php
namespace App\Servlets;

use Collei\Http\HttpServlet;
use Collei\Http\Request;

class IndexPageServlet extends HttpServlet
{
	public function index(Request $request)
	{
		return view('index');
	}

	public function pageNotFound(Request $request)
	{
		$this->session->flash(
			'error', 'Esta página não existe: "' . $request->uri . '"'
		);

		redirect('/sites/home');
	}

}
