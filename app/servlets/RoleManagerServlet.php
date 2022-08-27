<?php
namespace App\Servlets;

use App\Servlets\AdminServlet;

use App\Models\Role;

class RoleManagerServlet extends AdminServlet
{

	public function index()
	{
		$roles = Role::all();

		return view('admin.roles.list', [ 'roles' => $roles ]);
	}

	public function detail(int $role_id)
	{
		$role = Role::fromId($role_id);

		return view('admin.roles.item', [ 'role' => $role ]);
	}

	public function create(string $name, string $color = '#000000', string $icon = 'fas fa-user')
	{
		$new = Role::new();
		$new->name = substr($name,0,40);
		$new->color = substr($color,0,7);
		$new->icon = substr($icon,0,127);
		$new->save();

		redirect('/sites/admin/roles');
	}

	public function modify(int $role_id, string $name, string $color = '#000000', string $icon = 'fas fa-user')
	{
		$role = Role::fromId($role_id);
		$role->name = substr($name,0,40);
		$role->color = substr($color,0,7);
		$role->icon = substr($icon,0,127);
		$role->save();

		redirect('/sites/admin/roles');
	}

}


