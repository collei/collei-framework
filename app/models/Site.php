<?php
namespace App\Models;

use Collei\Database\Yanfei\Model;

class Site extends Model
{

	public function users()
	{
		$roles = $this->belongsTo(Role::class);

		return $roles;
	}

}

