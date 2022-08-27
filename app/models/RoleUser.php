<?php
namespace App\Models;

use Collei\Database\Yanfei\AssociativeModel;

class RoleUser extends AssociativeModel
{
	protected $table = 'role_user';

	protected $associates = [
		Role::class,
		Site::class,
		User::class,
	];

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function site()
	{
		return $this->belongsTo(Site::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

}

