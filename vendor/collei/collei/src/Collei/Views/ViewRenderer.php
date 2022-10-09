<?php
namespace Collei\Views;

use Collei\App\App;
use Collei\Http\Session;
use Collei\Views\ConstructBase;
use Collei\Views\ColleiViewException;
use Collei\Views\ViewValidator;
use Collei\Views\ViewLocator;
use Collei\Utils\Str;
use Collei\Utils\Files\TextFile;
use Collei\Database\Yanfei\Model;
use Collei\Database\Yanfei\ModelResult;
use Exception;
use Throwable;
use ParseError;
use Closure;

/**
 *	Embodies the view render methods
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class ViewRenderer
{
	/**
	 *	@var string $site_name
	 */
	private $site_name;

	/**
	 *	@var string $view_name
	 */
	private $view_name;

	/**
	 *	@var string $view_filename
	 */
	private $view_filename;

	/**
	 *	@var \Collei\App\App $app_instance
	 */
	private $app_instance;

	/**
	 *	@var string $loaded_source
	 */
	private $loaded_source;

	/**
	 *	@var string $source_info
	 */
	private $source_info = [];

	/**
	 *	@var string $source_errors
	 */
	private $source_errors = [];

	/**
	 *	@var array $extended_views
	 */
	private $extended_views = [];

	/**
	 *	Resolves the file location from view name
	 *
	 *	@param	string	$viewName
	 *	@param	\Collei\App\App	$appInstance
	 *	@param	string	&$fromSite
	 *	@return	string
	 */
	public static function locate(
		string $viewName, App $appInstance, string &$fromSite
	) {
		$site = $appInstance->getSite();
		$fromSite = '';
		$filename = ViewLocator::locateViewFile($viewName, $site, $fromSite);
		//
		if ($filename !== false) {
			return $filename;
		}
		//
		logerror(
			'view_processing',
			__METHOD__ . ": View $viewName not found on $site [$filename] "
		);
		//
		return false;
	}

	/**
	 *	Resolves the view @extends statements
	 *
	 *	@param	string	$source
	 *	@return	string
	 */
	private function processExtends(string $source)
	{
		/*
			1 - extract sections
					remove them from code
					store them at $temp
			2 - process 'extends' ->
					load that view file
					'render' the extendee (parent) view into the extender view:
			3 - apply sections in the new extender
					'render' the sections where needed
			4 - parse everything else (as it was just any other view file)
		*/
		$pattern0 = '#\@extends\(\'([\w\-]+(\.[\w\-]+)*)\'\)#';

		$patterns = [
			'#\@extend\(\'([\w\-]+(\.[\w\-]+)*)\'(\s*,\s*(\[(\s*\'\w+\'\s*=>\s*([^,]+),)*(\s*\'\w+\'\s*=>\s*([^\]]+))?\])?)?\)#',
			'#\@extends\(\'([\w\-]+(\.[\w\-]+)*)\'(\s*,\s*(\[(\s*\'\w+\'\s*=>\s*([^,]+),)*(\s*\'\w+\'\s*=>\s*([^\]]+))?\])?)?\)#'
		];

		$placement = '';
		$match = array();

		foreach ($patterns as $pattern)
		{
			if (preg_match_all($pattern, $source, $match) !== false)
			{
				foreach($match[1] as $mi => $view_name)
				{
					$search = $match[0][$mi];
					$arguments = $match[4][$mi] ?? '';

					if (!empty($arguments))
					{
						$arguments = @eval($arguments);
					}

					$renderer = new ViewRenderer($view_name, $this->app_instance);
					$replacement = $renderer->renderAssembled();

					$this->extended_views[] = [
						'view_name' => $view_name,
						'renderer' => $renderer,
					];

					$source = str_replace($search, $replacement, $source);
				}
			}
		}

		return $source;
	}

	/**
	 *	Extract sections from the source
	 *
	 *	@param	string	$source
	 *	@return	string
	 */
	private function extractSections(string $source)
	{
		$pattern = '#@section\(\'([\w\-]+?)\'\)(.*?)@endsection#s';
		$placement = '';
		$match = array();
		$sections = array();

		if (preg_match_all($pattern, $source, $match) !== false)
		{
			foreach($match[1] as $mi => $section_name)
			{
				$sections[$section_name] = $match[2][$mi];
			}
		}

		return $sections;
	}

	/**
	 *	Removes the already used sections
	 *
	 *	@param	string	$source
	 *	@return	string
	 */
	private function removeSections(string $source)
	{
		$pattern = '#@section\(\'([\w\-]+?)\'\)(.*?)@endsection#s';
		return preg_replace($pattern, '', $source);
	}

	/**
	 *	Resolves the @yield() statements
	 *
	 *	@param	string	$source
	 *	@param	array	$sections
	 *	@return	string
	 */
	private function applyYields(string $source, array $sections)
	{
		$pattern = '#@yield\(\'([\w\-]+)\'\)#';
		$placement = '';
		$match = array();

		if (preg_match_all($pattern, $source, $match) !== false)
		{
			foreach($match[1] as $mi => $content_name)
			{
				if (array_key_exists($content_name, $sections))
				{
					$search = $match[0][$mi];
					$replacement = $sections[$content_name];

					$source = str_replace($search, $replacement, $source);
				}
			}
		}

		return $source;
	}

	/**
	 *	Assemble the source from various source includes and yieldings
	 *
	 *	@param	string	$source
	 *	@return	string
	 */
	private function assemble(string $source)
	{
		$sections = $this->extractSections($source);
		$cleaned_source = $this->removeSections($source);
		$extended_source = $this->processExtends($cleaned_source);
		$assembled = $this->applyYields($extended_source, $sections);

		return $assembled;
	}

	/**
	 *	Parses the assembled source
	 *
	 *	@param	string	$assembled
	 *	@return	string
	 */
	private function parse(string $assembled)
	{
		$cons = ConstructBase::getSnippets();
		$parsed = $assembled;
		//
		foreach ($cons['pattern'] as $idx => $patt)
		{
			$repl = $cons['replacement'][$idx];
			$parsed = preg_replace($patt, $repl, $parsed);
		}
		//
		return $parsed;
	}

	/**
	 *	Extracts the variables to the resulting source
	 *
	 *	@param	array	...$stocks
	 *	@return	string
	 */
	private function extract(array ...$stocks)
	{
		$code = [];

		foreach ($stocks as $stock)
		{
			foreach ($stock as $n => $v)
			{
				$co = "\r\n" . ' $' . $n . ' = ';

				if (is_string($v))
					$co .= '"' . $v . '";';
				if (is_int($v))
					$co .= '' . $v . ';';
				if (is_float($v))
					$co .= '' . $v . '; ';
				if (is_bool($v))
					$co .= '' . ($v ? 'true' : 'false') . '; ';
				if (is_array($v))
					$co .= 'unserialize(\'' . serialize($v) . '\'); ';
				if (is_object($v))
				{
					if ($v instanceof Model)
					{
						$co .= 'unserialize(\'' . serialize($v) . '\'); ';
					}
				}

				$code[] = $co;
			}
		}

		return implode("\r\n", $code);
	}

	/**
	 *	Loads the view source
	 *
	 *	@param	string	$viewName
	 *	@param	\Collei\App\App	$instance
	 *	@return	void
	 */
	public function loadSource(string $viewName, App $instance)
	{
		$sitename = '';
		$filename = ViewRenderer::locate($viewName, $instance, $sitename);

		if (empty($filename) || ($filename === false))
		{
			logerror(
				'view_processing',
				"The view '$viewName' translated to a non-existing file."
			);

			throw new ColleiViewException(
				"view '$viewName' not found. Please check it carefully.",
				'Missing view'
			);
		}

		if ($errors = ViewValidator::for($filename)->getErrors())
		{
			$message = $errors[0]['description'] . ' at code "'
				. $errors[0]['code'] . '", at view file "'
				. $errors[0]['file'] . '", line ' . $errors[0]['line'] . '.';

			logerror('view_processing', $message);

			throw new ColleiViewException(
				$message, $this->view_filename,	$errors[0]['line']
			);
		}

		$file = new TextFile();
		$file->loadFrom($filename);

		$this->site_name = $sitename;
		$this->view_filename = $filename;
		$this->loaded_source = $file->getText();
	}

	/**
	 *	Instantiates the class
	 *
	 *	@param	string	$viewName
	 *	@param	\Collei\App\App	$instance
	 */
	public function __construct(string $viewName, App $instance)
	{
		$this->site_name = '';
		$this->view_name = $viewName;
		$this->app_instance = $instance;

		$this->loadSource($viewName, $instance);
	}

	/**
	 *	Assembles the current view into the generated content
	 *
	 *	@return	mixed
	 */
	public function renderAssembled()
	{
		return $this->assemble($this->loaded_source);
	}

	/**
	 *	If any, returns all validation errors found in the source.
	 *	Otherwise, returns false
	 *
	 *	@return	array|bool
	 */
	public function getErrors()
	{
		if (!empty($this->source_errors))
		{
			return $this->source_errors;
		}

		return false;
	}

	/**
	 *	Render the current view
	 *
	 *	@param	array	$variables
	 *	@return	mixed
	 */
	public function render(array $variables = array())
	{
		$assembled_source = $this->assemble($this->loaded_source);
		$parsed_source = $this->parse($assembled_source);
		$rendered_source = '';
		$current_session = Session::capture();
		//
		try
		{
			$everything = array_merge(
				$variables,
				$current_session->flashed(),
				$current_session->published()
			);
			//
			extract($everything, EXTR_OVERWRITE);
			ob_start();
			$parsed_source = " ?>$parsed_source<?php ";
			eval($parsed_source);
			$rendered_source = ob_get_clean();
		}
		catch (ParseError $pe)
		{
			$msg = 'Detected error: «'. $pe->getMessage()
				. '» at file ' . $this->view_filename
				. ', Line ' . $pe->getLine() . '.';
			$this->logError(
				$pe, __METHOD__, $msg, $assembled_source, $parsed_source, true
			);
		}
		catch (Throwable $t)
		{
			$msg = 'Detected error: «'. $t->getMessage()
				. '» at Line ' . $t->getLine()
				. " with Trace: «\r\n" . $t->getTraceAsString()
				. "\r\n » ";
			$this->logError(
				$t, __METHOD__, $msg, $assembled_source, $parsed_source, false
			);
		}
		//
		$this->parsed = $rendered_source;
		return $rendered_source;
	}

	/**
	 *	Logs errors with view source debugging
	 *
	 *	@param	\Throwable	$t
	 *	@param	string		$method
	 *	@param	string		$message
	 *	@param	string		$assembledSource = ''
	 *	@param	string		$parsedSource = ''
	 *	@return	void
	 */
	private function logError(
		Throwable $t, string $method, string $message,
		string $assembledSource = '',
		string $parsedSource = '',
		bool $throwException = false
	)
	{
		$msg = $t->getMessage();
		$file = $this->view_filename;
		$line = $t->getLine();
		$trace = $t->getTraceAsString();

		logerror('view_processing', $method . ': ' . $message);

		logerror(
			$method . ' >> [ORIGINALSOURCE] ',
			"\r\n" . Str::withLineNumbers($assembledSource)
		);
		logerror(
			$method . ' >> [PARSEDSOURCE] ',
			"\r\n" . Str::withLineNumbers($parsedSource)
		);

		throw new ColleiViewException($message, $file, $line);
	}

}


