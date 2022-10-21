<?php 
namespace Collei\Http;

use Collei\Http\Session;
use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Views\View;
use Collei\App\Servlets\Servlet;
use Collei\App\Services\Service;
use Collei\Http\Uploaders\FileUploadRequest;
use Collei\Contracts\Capturable;
use Collei\Contracts\Makeable;
use Collei\App\Performers\Injectors\DependencyInjector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionUnionType;
use Collei\Exceptions\InsuficientArgumentCountException;
use InvalidArgumentException;
use stdClass;

/**
 *	Encapsulates a HTTP servlet
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-05-xx
 */
abstract class HttpServlet extends Servlet
{
	/**
	 *	@var \Collei\Http\Request $request
	 */
	protected $request;

	/**
	 *	@var \Collei\Http\Session $session
	 */
	protected $session;

	/**
	 *	prepares request based upon the content returned by the handler
	 *
	 *	@param	mixed	$content
	 *	@return	mixed
	 */
	private function prepareResponse($content)
	{
		if ($content instanceof Response) {
			return $content;
		} elseif ($content instanceof Model) {
			return $content;
		} elseif ($content instanceof View) {
			return $content;
		} else {
			$response = Response::make();
			//
			if (is_array($content) || is_object($content)) {
				$response->setBody(json_encode($content));
			} else {
				$response->setBody($content);
			}
			//
			return $response;
		}
	}

	/**
	 *	Instance builder
	 *
	 *	@param	\Collei\Http\Request	$request
	 *	@return	void
	 */
	protected function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 *	Captures the session (if any) or starts a brand new one
	 *
	 *	@return	\Collei\Http\Session
	 */
	protected function captureSession()
	{
		return ($this->session = Session::capture());
	}

	/**
	 *	Instance builder
	 *
	 *	@param	\Collei\Http\Request	$request
	 */
	public function __construct(Request $request)
	{
		$this->setRequest($request);
		$this->captureSession();
		$this->init();
	}

	/**
	 *	Instance finalizer
	 *
	 *	@return	void
	 */
	public function __destruct()
	{
		$this->term();
	}

	/**
	 *	Invokes the specified method with arguments
	 *
	 *	@param	string	$method
	 *	@param	mixed	$parameters
	 *	@return	mixed
	 */
	public final function callAction(string $method, $parameters)
	{
		return $this->prepareResponse(
			parent::callAction($method, $parameters)
		);
	}

	/**
	 *	Handles the request
	 *
	 *	@param	\Collei\Http\Request	$request
	 *	@return	mixed
	 */
	public function handle(Request $request)
	{
		// does nothing here, but it may be overriden by subclasses if needed
		return Response::make();
	}

	/**
	 *	Inserts a Request instance into a HttpServlet instance 
	 *
	 *	@param	\Collei\Http\HttpServlet	$request
	 *	@param	\Collei\Http\Request		$request
	 *	@return	\Collei\Http\HttpServlet
	 */
	public static function assignRequestTo(
		HttpServlet $servlet, Request $request
	) {
		$servlet->request = $request;
		//
		return $servlet;
	}

	/**
	 *	Helper for makeServlet() method. Binds additional classes needed
	 *	for the constructor parameters.
	 *
	 *	@param	string	$type
	 *	@param	\Collei\App\Performers\Injectors\DependencyInjector	$injector
	 *	@return	void
	 */
	private static function bindTypeInto(
		string $type, DependencyInjector $injector
	) {
		if (class_exists($type)) {
			if (is_a($type, Service::class, true)) {
				$injector->bind($type, 'make', true);
			} elseif (is_a($type, Request::class, true)) {
				$injector->bind($type, 'capture', true);
			} else {
				$injector->bind($type);
			}
		}
	}

	/**
	 *	Creates a new HttpServlet instance (or subclass of it)
	 *	with injected instances of arguments, if any
	 *
	 *	@param	\Collei\Http\Request		$request
	 *	@return	\Collei\Http\HttpServlet
	 */
	private static function makeServlet(Request $request)
	{
		$calledClass = get_called_class();
		$injector = (new DependencyInjector($calledClass))
			->bind(FileUploadRequest::class, 'capture', true)
			->bind(Service::class, 'make', true)
			->bind($calledClass, 'make', true)
			->addValue(get_class($request), $request);
		//
		$refParams = $injector->getParameterReflectors();
		foreach ($refParams as $refParam) {
			$typeDef = $refParam->getType();
			//
			if ($typeDef instanceof ReflectionUnionType) {
				foreach ($typeDef->getTypes() as $subType) {
					self::bindTypeInto($subType, $injector);
				}
			} else {
				self::bindTypeInto((string)$typeDef, $injector);
			}
		}
		//
		foreach ($request->getParameterNames() as $name) {
			$injector->addValue(
				$name, $request->getParameter($name)
			);
		}
		//
		return $injector->call();
	}

	/**
	 *	Creates a new instance of the class. 
	 *
	 *	@static
	 *	@return	instanceof \Collei\Http\HttpServlet
	 */
	public static function make()
	{
		$args = func_get_args();
		//
		if (isset($args[0])) {
			if (is_a($args[0], Request::class, true)) {
				return static::makeServlet($args[0]);
			}
		}
		//
		return static::makeServlet(Request::capture());
	}

}


