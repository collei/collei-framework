<?php

namespace App\Models;

use Collei\Database\Yanfei\Model;

/**
 *	This embodies entity backed by the corresponding DB table
 *	Basic capabilities available through base model.
 *
 */
class ContactTag extends Model
{
	protected $table = 'contact_tag';

	protected $associates = [
		Contact::class,
		Tag::class,
	];

}
