<?php
namespace App\Models;

use Collei\Auth\Models\AuthUser;

use App\Models\Site;

class User extends AuthUser
{

	public function roles()
	{
		return $this->belongsToMany(Role::class);
	}

	public function sites()
	{
		return $this->hasMany(RoleUser::class)->filter(function($roleUser){
			return $roleUser->site;
		}, Site::class);
	}

	public function permissions()
	{
		return $this->hasMany(RoleUser::class);
	}

	public function hasRole($roleName, $siteName)
	{
		if (guest())
		{
			return false;
		}

		foreach($this->permissions as $permission)
		{
			if ($permission->role->name == $roleName && $permission->site->name == $siteName)
			{
				return true;
			}
		}
		return false;
	}


}

