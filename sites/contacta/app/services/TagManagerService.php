<?php

namespace App\Services;

use Collei\App\Services\Service;
use Collei\Support\Arr;
use Collei\Support\Str;

use App\Models\Tag;

/**
 *	This allow reuse of code and funcionality injection.
 *	Basic capabilities available through base service.
 *
 */
class TagManagerService extends Service
{

	public function createTag(string $name, string $color)
	{
		$tag = new Tag();
		$tag->name = $name;
		$tag->color = $color;
		$tag->save();
		//
		return $tag;
	}

	public function deleteTag(int $id)
	{
		$tag = Tag::fromId($id);
		$tag->remove();
		//
		return $tag;
	}


}
