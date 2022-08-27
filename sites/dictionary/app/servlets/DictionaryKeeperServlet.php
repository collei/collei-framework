<?php

namespace App\Models\Servlets;

use Collei\Http\HttpServlet;
use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Http\DataResponse;

use App\Models\Entry;
use App\Models\Meaning;
use App\Models\Speechpart;
use App\Services\DictionaryKeeperService;

class DictionaryKeeperServlet extends HttpServlet
{
	private function importer($agent)
	{
		return $agent->importData();
	}

	private function exporter($agent, &$qtd)
	{
		return $agent->exportData($qtd);
	}

	public function home(Request $request)
	{
		return view('index');
	}

	public function list()
	{
		ini_set("max_execution_time", 900);

		$lista = Entry::all('entry asc');
		$spl = Speechpart::all();

		return view(
			'entries.list',
			['entries' => $lista, 'speechparts' => $spl ]
		);
	}

	public function entry($id)
	{
		return DataResponse::make('application/json')
					->setBody(
						Entry::fromId($id)->asJson()
					);
	}

	public function updateIt(
		$id, $entry, $speech, $meanings, $origin, $origin_from,
		DictionaryKeeperService $agent
	)
	{
		$agent->updateEntry(
			$id, $entry, $speech, $meanings, $origin, $origin_from
		);

		return $this->list();
	}

	public function import(Request $request, DictionaryKeeperService $agent)
	{
		$quantos = $this->importer($agent);

		$this->session->flash(
			'message',
			'Imported <b>' . $quantos . '</b> entries successfully. '
		);

		redirect('/home');
	}

	public function export(Request $request, DictionaryKeeperService $agent)
	{
		$quantos = 0;
		$data = $this->exporter($agent, $quantos);

		return DataResponse::make('application/json')
					->setBody($data)
					->downloadAs('dic.json');
	}

	public function newEntryStart(Request $request)
	{
		return view('entries.new');
	}

	public function newEntry(
		$entry, $speechpart,
		$meanings = null, $origin = null, $origin_from = null,
		DictionaryKeeperService $adder
	)
	{
		$ent = $adder->addEntry(
			$entry, $speechpart, [$meanings], $origin, $origin_from
		);

		return view('entries.list', ['entry' => $ent]);
	}

}

