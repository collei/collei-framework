<?php

namespace App\Models\Services;

use Collei\App\Services\Service;
use Collei\Http\Request;
use Collei\Support\Str;

use App\Models\Entry;
use App\Models\Meaning;
use App\Models\Speechpart;

include 'dicdata.php';


class DictionaryKeeperService extends Service
{
	private $request;

	public function addEntry(
		$entry, $speechpart, array $meanings = [],
		$origin = null, $origin_from = null
	)
	{
		$ent = new Entry();
		$ent->entry = $entry;
		$ent->speechpartId = $speechpart;
		$ent->origin = $origin ?? '';
		$ent->originFrom = $origin_from ?? '';
		$ent->save();

		foreach ($meanings as $meaning)
		{
			$mea = new Meaning();
			$mea->entryId = $ent->id;
			$mea->meaning = $meaning;
			$mea->save();
		}

		return $ent;
	}

	public function importData()
	{
		ini_set("max_execution_time", 600);
		
		$portuguese = getPortugueseData();
		$speeches = getSpeechparts();
		$quants = 0;


		foreach ($portuguese as $ent)
		{
			$pos = $speeches[$ent['pos']][0];

			$this->addEntry($ent['entry'], $pos, $ent['meaning'], $ent['origin'], $ent['originTerm']);
			++$quants;

			$related = $ent['related'] ?? null;

			if (!is_null($related))
			{
				foreach ($related as $rel)
				{
					$this->addEntry($rel['entry'], $pos, $rel['meaning']);
					++$quants;
				}
			}
		}

		return $quants;
	}

	public function exportData(&$count)
	{
		$dic = Entry::all('entry asc');
		$count = $dic->size();
		$entries = [];

		foreach ($dic as $ent) {
			$entries[] = [
				'entry' => $ent->entry,
				'speechpart' => [
					'id' => $ent->partof()->id,
					'name' => $ent->partof()->description,
					'abbreviated' => $ent->partof()->abbreviated,
				],
				'meanings' => $ent->meanings()->filterData('meaning')
			];
		}

		return json_encode($entries);
	}

	public function updateEntry(
		$id, $entry, $speech, $meanings,
		$origin = null, $origin_from = null
	)
	{
		$ent = Entry::fromId($id);
		$ent->entry = $entry;
		$ent->speechpartId = $speech;
		$ent->origin = $origin ?? '';
		$ent->originFrom = $origin_from ?? '';
		$ent->save();

		$meaningList = Str::linesToArray($meanings);

		$entryMeanings = $ent->meanings();
		$entryMeaningsArray = $ent->meaningsAsArray();

		// remove meanings removed by user from there
		foreach ($entryMeanings as $em)
		{
			if (!Str::in($em->meaning, $meaningList))
			{
				$em->remove();
			}
		}

		// add some meanings the user added there 
		foreach ($meaningList as $meant)
		{
			if (!Str::in($meant, $entryMeaningsArray))
			{
				$mea = new Meaning();
				$mea->entryId = $id;
				$mea->meaning = $meant;
				$mea->save();
			}
		}
	}


}
