<?php

namespace App\Servlets;

use Collei\Http\HttpServlet;
use Collei\Http\Request;
use Collei\Http\DataResponse;

use App\Services\ContactRetainerService;
use App\Services\TagManagerService;

use Collei\Utils\Parsers\RawRequestBodyParser;

/**
 *	this processes requests and returns results.
 *	Basic capabilities available through base servlet.
 *
 */
class ContactAjaxServlet extends HttpServlet
{
	private $contactRetainer;
	private $tagManager;

	public function __construct(
		Request $request,
		ContactRetainerService $contactRetainer,
		TagManagerService $tagManager
	)
	{
		parent::__construct($request);

		$this->contactRetainer = $contactRetainer;
		$this->tagManager = $tagManager;
	}

	public function index()
	{
		return view('index');
	}

	public function pageNotFound()
	{
		$this->session->flash(
			'error', 'This page does not exist: ' . $this->request->uri
		);

		redirect('/home');
	}

	public function createTag(string $name, string $color = null)
	{
		$tag = $this->tagManager->createTag($name, $color ?? '#000000');

		return DataResponse::make('application/json')->setBody(
			'{"tagId":'.$tag->id.',"tag":'.$tag->toJson().'}'
		);
	}

	public function deleteTag()
	{
		$id = $this->request->tag;
		$tag = Tag::fromId($id);
		$tag->remove();

		$message = ('Tag "' . $tag->name . '" successfully removed. ');

		return DataResponse::make('application/json')->setBody(
			'{"message":"' . $message . '",'
			.	'"tagId":' . $tag->id . '",'
			.	'"tag":' . $tag->toJson() . '}'
		);
	}


}
