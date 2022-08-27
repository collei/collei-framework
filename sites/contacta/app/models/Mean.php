<?php
namespace App\Models;

use App\Models\MeanType;
use App\Models\Contact;

use Collei\Database\Yanfei\Model;

class Mean extends Model
{
	public function type()
	{
		return $this->belongsTo(MeanType::class);
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class);
	}

}
