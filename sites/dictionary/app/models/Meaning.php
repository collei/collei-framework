<?php
namespace App\Models;

use App\Models\Entry;

use Collei\Database\Yanfei\Model;

class Meaning extends Model
{
	public function entry()
	{
		return $this->belongsTo(Entry::class);
	}

}
