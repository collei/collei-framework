<?php
namespace App\Servlets;

use Collei\App\Agents\Storage;

use App\Servlets\AdminServlet;
use App\Models\User;

class AdminPanelServlet extends AdminServlet
{
	public function index()
	{
		$users = User::all();

		$sto = Storage::get();

		return view('admin.panel', [
			'testings' => [
				'storage' => $sto,
				'users' => $users,
			]
		]);
	}

}


