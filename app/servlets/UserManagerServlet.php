<?php
namespace App\Servlets;

use App\Servlets\AdminServlet;
use Collei\Http\DataResponse;

use App\Models\{User, Role, Site, RoleUser};

class UserManagerServlet extends AdminServlet
{

	public function index()
	{
		$users = User::all();
		//
		return view('admin.users.list', [ 'users' => $users ]);
	}

	public function detail(int $user_id)
	{
		$usr = User::fromId($user_id);
		$sites = Site::all();
		$roles = Role::all();
		//
		return view('admin.users.item', [
			'usr' => $usr,
			'rolelist' => $roles,
			'sitelist' => $sites
		]);
	}

	public function setRoleOnSite(int $user_id, int $site_id, int $role_id)
	{
		$user = User::fromId($user_id);
		//
		foreach ($user->permissions as $perm) {
			if ($perm->site->id == $site_id) {
				if ($perm->role->id != $role_id) {
					$perm->remove();
				}
			}
		}
		//
		$new = RoleUser::new();
		$new->siteId = $site_id;
		$new->roleId = $role_id;
		$new->userId = $user_id;
		$new->save();
	}

	public function setNewPassword(
		int $user_id, string $new_password, int $must_change = 0
	) {
		$user = User::fromId($user_id);
		$user->setPassword($new_password);
		$user->mustChange = $must_change;
		$user->save();
		//
		return DataResponse::make('application/json')->setBody(
			json_encode([
				'message' => 'Password changed successfully.'
			])
		);
	}

	public function removeMFA(int $user_id)
	{
		$user = User::fromId($user_id);
		$user->mfaEnabled = 0;
		$provider = $user->mfaProvider;
		$user->mfaProvider = '';
		$user->mfaSecret = '';
		$user->save();
		//
		return DataResponse::make('application/json')->setBody(
			json_encode([
				'message' => $provider . ' MFA successfully removed.'
			])
		);
	}

}


