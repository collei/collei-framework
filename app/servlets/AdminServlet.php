<?php
namespace App\Servlets;

use Collei\Http\HttpServlet;
use Collei\Http\Request;


class AdminServlet extends HttpServlet
{
	public function __construct(Request $request)
	{
		parent::__construct($request);
	}

	protected function before()
	{
		if (isset($this->session->user)) {
			if ($this->session->user->hasRole('admin', 'plat')) {
				return true;
			}
		}
		//
		$this->session->flash(
			'error',
			"Área administrativa de PLAT: Acesso permitido apenas a " .
				" administradores gerais de PLAT."
		);
		redirect('/sites/home');
	}

}


