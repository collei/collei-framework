<?php
namespace App\Servlets;

use App\Servlets\AdminServlet;
use App\Services\SiteEngineService;
use Collei\Http\Request;

use App\Models\Site;

class SiteManagerServlet extends AdminServlet
{
	private $engine = null;	

	public function __construct(Request $request, SiteEngineService $svc)
	{
		parent::__construct($request);
		$this->engine = $svc;
	}

	public function index()
	{
		$sites = Site::all();
		//
		return view('admin.sites.list', [ 'sites' => $sites ]);
	}

	public function detail(int $site_id)
	{
		$site = Site::fromId($site_id);
		//
		return view('admin.sites.item', [ 'site' => $site ]);
	}

	public function create(
		string $name, string $description = '', int $is_down = 0,
		int $is_admin_only = 0
	)
	{
		$new = Site::new();
		$new->name = substr($name,0,40);
		$new->description = substr($description,0,250);
		$new->isDown = ($is_down > 0);
		$new->isAdminOnly = ($is_admin_only > 0);
		$new->save();
		//
		redirect('/sites/admin/siteman');
	}

	public function modify(
		int $site_id, string $description = '', int $is_down = null,
		int $is_admin_only = null
	)
	{
		$site = Site::fromId($site_id);
		$site->description = substr($description,0,250);
		$site->isDown = ($is_down > 0) ? 1 : 0;
		$site->isAdminOnly = ($is_admin_only > 0) ? 1 : 0;
		$site->save();
		//
		redirect('/sites/admin/siteman');
	}

	public function engineView(string $engine)
	{
		$elements = [
			'models' => $this->engine->listModels($engine),
			'servlets' => $this->engine->listServlets($engine),
			'services' => $this->engine->listServices($engine),
			'filters' => $this->engine->listFilters($engine),
			'commands' => $this->engine->listCommands($engine),
		];
		//
		return view('admin.sites.engine.elements', [
			'engine' => $engine,
			'elements' => $elements,
		]);
	}

	public function engineAddFile($engine, $element, $classname)
	{
		$this->engine->createFile($classname, $engine, $element);
		//
		redirect(
			route('plat-adm-site-engine-view', ['engine' => $engine])
		);
	}

}


