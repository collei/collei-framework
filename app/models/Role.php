<?php
namespace App\Models;

use Collei\Database\Yanfei\Model;

class Role extends Model
{
	public function users()
	{
		return $this->belongsToMany(User::class);
	}

}

