<?php
namespace App\Models;

use Collei\Database\Yanfei\Model;

use App\Models\Meaning;
use App\Models\Speechpart;

class Entry extends Model
{
	protected $table = 'entries';

	protected $related = [
		'speechPart' => 'partof',
		'meanings' => 'meaningsAsArray',
	];

	public function partof()
	{
		return $this->belongsTo(Speechpart::class);
	}

	public function meanings()
	{
		return $this->hasMany(Meaning::class);
	}

	public function meaningsAsArray()
	{
		return $this->meanings()->filterData('meaning');
	}

	public function meaningsAsString()
	{
		return implode(', ', $this->meaningsAsArray());
	}

}
