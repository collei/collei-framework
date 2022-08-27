<?php 
namespace Collei\Views;

use Collei\App\App;
use Collei\Views\ConstructBase;
use Collei\Views\ViewRenderer;
use Collei\Utils\Files\TextFile;
use Collei\Views\ColleiViewException;

/**
 *	Embodies the view handling methods
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class View
{
	/**
	 *	@var \Collei\App\App $app_instance
	 */
	private $app_instance = null;

	/**
	 *	@var string $view_name
	 */
	private $view_name = '';

	/**
	 *	@var array $variables
	 */
	private $variables = [];

	/**
	 *	Builds and initializes
	 *
	 *	@param	string	$viewName
	 *	@param	array	$variables
	 */
	public function __construct(string $viewName, array $variables = null)
	{
		$app = App::getInstance();
		//
		$this->view_name = $viewName;
		$this->app_instance = $app;
		//
		if (!is_null($variables)) {
			$this->assignData($variables);
		}
	}

	/**
	 *	Assigns data through variables
	 *
	 *	@param	array	$variables
	 *	@return	mixed
	 */
	public function assignData(array $variables)
	{
		if (!is_null($variables)) {
			foreach ($variables as $n => $v) {
				$this->variables[$n] = $v;
			}
		}
	}

	/**
	 *	Fetches the variables into the content
	 *
	 *	@param	array	$variables
	 *	@return	mixed
	 */
	public function fetch(array $variables = null)
	{
		if (is_null($variables)) {
			$variables = $this->variables;
		}
		//
		$renderer = new ViewRenderer(
			$this->view_name, $this->app_instance
		);
		//
		return $renderer->render($variables);
	}

}


