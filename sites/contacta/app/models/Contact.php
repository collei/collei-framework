<?php
namespace App\Models;

use App\Models\ContactType;
use App\Models\Mean;

use Collei\Database\Yanfei\Model;

class Contact extends Model
{
	private $mean_list = null;

	public function kind()
	{
		return $this->belongsTo(ContactType::class);
	}

	public function meanList()
	{
		if (empty($this->mean_list)) {
			$this->mean_list = $this->hasMany(Mean::class);
		}
		//
		return $this->mean_list;
	}

	public function photoOr($alternative = null)
	{
		return $this->avatar
			?? $alternative
			?? 'http://kazuha.local/sites/resources/images/noimage.jpg';
	}

}
