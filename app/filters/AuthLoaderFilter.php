<?php
namespace App\Filters;

use Collei\Http\Filters\Filter;

use App\Models\User;

class AuthLoaderFilter extends Filter
{
	private function userFromId($uid)
	{
		$user = User::fromId($uid);
		$site = null;
		$permissions = $user->permissions;
		$role_site = [];

		foreach ($permissions as $permission)
		{
			$role_site[] = [$permission->site, $permission->role];

			if (($permission->site->name ?? '') == site())
			{
				$site = $permission->site;
			}
		}

		$this->session->user = $user;
		$this->session->permissions = $permissions;
		$this->session->role_site = $role_site;
		$this->session->site = $site;

		return $user;
	}

	/**
	 *	allows ignore this filter for certain requests
	 *
	 *	@return array 	List of rules in format '<verb> <uri>'. 
	 *					<verb> must be a valid HTTP verb or an asterisk.
	 *					<uri> must be a valid route URI or an asterisk.
	 *					Both are optional. An empty line or a '* *' (astyerisk + space + asterisk) line disables the filter
	 */
	public function except()
	{
		return [
			'* /mfa-logon',
		];
	}

	public function filter()
	{
		if (isset($this->session->uid))
		{
			$user = $this->userFromId($this->session->uid);

			$this->session->publish('user', $user);
			$this->request->setAttribute('authenticated', true);
		}
		else
		{
			$nullUser = new User();
			$nullUser->name = 'guest';

			$this->session->publish('user', $nullUser);
			$this->request->setAttribute('authenticated', false);
		}

		return true;
	}

}

