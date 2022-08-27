<?php

namespace App\Services;

use Collei\Services\Service;
use Collei\Http\Request;
use Collei\Http\Uploaders\FileUploadRequest;
use Collei\Utils\Str;

use App\Models\Mean;
use App\Models\Contact;

class ContactRetainerService extends Service
{
	private $request;

	public function saveUploaded(FileUploadRequest $upload)
	{
		if (!$upload->hasFiles())
		{
			return '';
		}

		$arquivo = $upload->getUploadedFiles()->first();
		$destino = resourceGround('images/ava');

		if (!$arquivo->moveTo($destino, Str::random(24)))
		{
			return '';
		}

		return resource('images/ava') . '/' . $arquivo->savedName;
	}

	public function createContact($name, $contact_type, FileUploadRequest $upload)
	{
		$person = new Contact();
		$person->name = $name;
		$person->contactTypeId = $contact_type;
		if ($upload->hasFiles())
		{
			$person->avatar = $this->saveUploaded($upload);
		}
		$person->save();
	}

	public function createMean($mean, $detail, $mean_type, $contact_id)
	{
		$m = new Mean();
		$m->contactId = $contact_id;
		$m->mean = trim($mean);
		$m->detail = trim($detail);
		$m->meanTypeId = $mean_type;
		$m->save();
	}

	public function editContact($id, $name, $contact_type, FileUploadRequest $upload)
	{
		$person = Contact::fromId($id);
		$person->name = $name;
		$person->contactTypeId = $contact_type;
		if ($upload->hasFiles())
		{
			$person->avatar = $this->saveUploaded($upload);
		}
		$person->save();
	}

	public function deleteContact($id)
	{
		$person = Contact::fromId($id);
		$meanList = $person->meanList();
		//
		foreach ($meanList as $mean)
		{
			$mean->remove();
		}
		//
		$person->remove();
		//
		return $person;
	}

	public function editMean($id, $mean, $detail, $meanType)
	{
		$m = Mean::fromId($id);
		$m->mean = trim($mean);
		$m->detail = trim($detail);
		$m->meanTypeId = $meanType;
		$m->save();

		return $m;
	}

	public function deleteMean($id)
	{
		$m = Mean::fromId($id);
		$m->remove();

		return $m;
	}

}
