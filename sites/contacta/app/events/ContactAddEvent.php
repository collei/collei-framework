<?php

namespace App\Events;

use Collei\Events\EventInterface;
use App\Models\Contact;

/**
 *	@author	YOUR-NAME-HERE	<your-email-address@domain.com>
 *	@since	yyyy-mm-dd
 *
 *	Describe your event here, and for what it does exist.
 */
class ContactAddEvent implements EventInterface
{
	private $contact;

	public function __construct(Contact $contact)
	{
		$this->contact = $contact;
	}

	public function getContact()
	{
		return $this->contact;
	}

}
