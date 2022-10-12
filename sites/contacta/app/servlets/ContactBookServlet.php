<?php

namespace App\Servlets;

use Collei\Http\HttpServlet;
use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Utils\Paging\PagingInfo;
use Collei\Http\DataResponse;

use Collei\Filesystem\Folder;
use Collei\Http\Uploaders\FileUploadRequest;
use Collei\App\Agents\Storage;

use App\Models\Contact;
use App\Models\ContactType;
use App\Models\Mean;
use App\Models\MeanType;
use App\Models\Tag;
use App\Services\ContactRetainerService;
use App\Services\TagManagerService;
use App\Services\QrCodeService;

use Collei\Utils\Values\Capsule;
use Collei\Utils\Parsers\RawRequestBodyParser;

use App\Events\ContactAddEvent;
use App\Listeners\ContactAddListener;
use Collei\Events\Dispatchers\EventDispatcher;
use Collei\Events\Providers\ListenerProvider;

class ContactBookServlet extends HttpServlet
{
	private $contactRetainer;
	private $tagManager;
	private $qrcoder;
	private $listenerProvider;

	public function __construct(
		Request $request,
		ContactRetainerService $contactRetainer,
		TagManagerService $tagManager,
		QrCodeService $qrcode,
		ListenerProvider $listenerProvider
	) {
		parent::__construct($request);
		//
		$this->plugin_list = plat_plugin_list_info();
		$this->contactRetainer = $contactRetainer;
		$this->tagManager = $tagManager;
		$this->qrcoder = $qrcode;
		$this->listenerProvider = $listenerProvider;
		//
		$this->listenerProvider->addListener(
			ContactAddEvent::class, new ContactAddListener()
		);
	}

	public function home()
	{
		$here = Folder::make(Folder::realize('../../'));
		$there = Folder::make(Folder::realize('/sites'));
		$overThere = $there->getFolder($razernode = 'razernode');

		($sto = Storage::get())->makeTreeIfNotExists($razernode);

		$pasta_dentro = $sto->getFolder($razernode);

		$texto = date('Y-m-d H:i:s') . ' -> Qr code gerado com sucesso ou não?'
			. ' Isto é um teste de integração de outros códigos dentro do Plat'
			. ' Collei enabledor de
			 sites.';

		$qrcode = $this->qrcoder->generate($texto);
		//
		$caps = Capsule::from([
			'here' => __FILE__,
			'numero' => 1234,
			'texto' => $texto,
			'qrcode' => $qrcode,
			'there' => $here,
			'althere' => $there,
			'overThere' => $overThere,
			'sto' => $sto,
			'pasta_dentro' => $pasta_dentro,
			'rrp' => new RawRequestBodyParser()
		]);
		//
		return view('index', [
			'congelado' => [
				'caps' => $caps,
				'sto' => $sto,
				'pasta_dentro' => $pasta_dentro,
			],
			'qrcodeuri' => $qrcode->getDataUri(),
			'clientes' => ['Washington','Wellington','Wesley','Franz'],
		]);
	}

	public function index(int $page = 1, int $pageSize = 10)
	{
		($paging = new PagingInfo(
			$pageSize, $rowc = Contact::count()
		))->setCurrent($page);
		//
		$people = Contact::paged($paging->current, $pageSize, 'id asc');
		$types = ContactType::all();
		//
		return view('book.contacts', [
			'sitepart' => 'listing',
			'people' => $people,
			'types' => $types,
			'congelado' => __FILE__,
			'pagination' => $paging,
		]);
	}

	public function indexAjax(int $page = 1)
	{
		($paging = new PagingInfo(
			$pageSize = 10, $rowc = Contact::count()
		))->setCurrent($page);
		//
		$people = Contact::paged($paging->current, $pageSize, 'id asc');
		//
		return DataResponse::make('application/json')->setBody(
			'{"paging":'.$paging->toJson().',"data":'.$people->toJson().'}'
		);
	}

	public function detail(int $id)
	{
		$means = ($person = Contact::fromId($id))->meanList();
		$types = MeanType::all();

		return view('book.contact-means', [
			'sitepart' => 'detail',
			'contact' => $person,
			'means' => $means,
			'types' => $types,
		]);
	}

	public function search()
	{
		return view('misc.search', [
			'sitepart' => 'search.home',
			'search' => '',
		]);
	}

	public function doSearch($search = '')
	{
		$results = Contact::join(Mean::class, 'contact_id')
						->where()
						->like('means.mean', $search)
						->or()->like('means.detail', $search)
						->or()->like('contacts.name', $search)
						->gatherAs(Contact::class);
		//
		return view('misc.search', [
			'sitepart' => 'search.do',
			'search' => $search,
			'people' => $results,
		]);
	}

	public function create(
		string $name, FileUploadRequest $upload, $contact_type = 1
	) {
		$person = $this->contactRetainer->createContact(
			$name, $contact_type, $upload
		);
		//
		EventDispatcher::to($this->listenerProvider)->dispatch(
			new ContactAddEvent($person)
		);
		//
		$this->session->flash(
			'message',
			concat('Contact \'', $name, '\' successfully registered.')
		);
		redirect('/people');
	}

	public function edit(
		int $id, string $name, $contact_type, FileUploadRequest $upload
	) {
		$this->contactRetainer->editContact($id, $name, $contact_type, $upload);
		//
		$this->session->flash(
			'message',
			concat('Contact \'', $name, '\' was updated successfully.')
		);
		redirect('/people');
	}

	public function delete(int $id)
	{
		$person = $this->contactRetainer->deleteContact($id);
		//
		$this->session->flash(
			'message',
			concat('Contact \'', $person->name, '\' was removed.')
		);
		redirect('/people');
	}

	public function createMean(
		int $contact_id, string $mean = null, string $detail = null,
		$mean_type = 1
	) {
		$this->contactRetainer->createMean(
			$mean, $detail, $mean_type, $contact_id
		);
		//
		$this->session->flash(
			'message', "Mean '$mean' successfully registered. "
		);
		redirect("/people/{$contact_id}/means");
	}

	public function editMean(
		int $mean_id, int $contact_id,
		$mean = null, $detail = null, $mean_type = null
	) {
		$this->contactRetainer->editMean($mean_id, $mean, $detail, $mean_type);
		//
		$this->session->flash(
			'message', "Contact #{$contact_id} was successfully updated."
		);
		redirect("/people/{$contact_id}/means");
	}

	public function deleteMean(int $mean_id, int $contact_id)
	{
		$mean = $this->contactRetainer->deleteMean($mean_id);
		//
		$this->session->flash(
			'message', concat(
				'Contact #', $contact_id,
				' was successfully updated by removing the mean #',
				$mean->mean
			)
		);
		redirect("/people/{$contact_id}/means");
	}

	public function listTags()
	{
		return view('book.tags', [
			'tags' => Tag::all(),
		]);
	}

	public function createTag(string $name, string $color = null)
	{
		$tag = $this->tagManager->createTag($name, $color ?? '#000000');
		//
		$this->session->flash(
			'message', "Tag '{$name}' successfully created. "
		);
		redirect("/tags");
	}

	public function editTag(string $name, string $color = null)
	{
		$old_name = ($tag = Tag::fromId($id = $this->request->tag))->name;
		$tag->name = $name;
		//
		if (!empty($color)) {
			$tag->color = $color;
		}
		//
		$tag->save();
		//
		$message = ($name != $old_name)
			? "Tag '{$old_name}' renamed to '{$name}' successfully. "
			: "Tag '{$old_name}' successfully updated. ";
		//
		$this->session->flash('message', $message);
		redirect("/tags");
	}

	public function deleteTag()
	{
		($tag = Tag::fromId($id = $this->request->tag))->remove();
		//
		$message = "Tag '{$tag->name}' successfully removed. ";
		//
		$this->session->flash('message', $message);
		redirect("/tags");
	}

}

