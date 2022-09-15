<?php 
namespace Collei\Http;

use Collei\Http\Session;
use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Views\View;
use Collei\Servlets\Servlet;
use Collei\Services\Service;
use Collei\Http\Uploaders\FileUploadRequest;
use Collei\Pacts\Capturable;
use Collei\Pacts\Makeable;
use Collei\App\Performers\Injectors\DependencyInjector;
use Persisto\Model\Model;
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
	public static function assignRequestTo(HttpServlet $servlet, Request $request)
	{
		$servlet->request = $request;
		//
		return $servlet;
	}

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
					if (class_exists($subType)) {
						if (is_a($subType, Service::class, true)) {
							$injector->bind($subType, 'make', true);
						} elseif (is_a($subType, Request::class, true)) {
							$injector->bind($subType, 'capture', true);
						} else {
							$injector->bind($subType);
						}
					}
				}
			} else {
				$typeStr = (string)$typeDef;
				if (class_exists($typeStr)) {
					if (is_a($typeStr, Service::class, true)) {
						$injector->bind($typeStr, 'make', true);
					} elseif (is_a($typeStr, Request::class, true)) {
						$injector->bind($typeStr, 'capture', true);
					} else {
						$injector->bind($typeStr);
					}
				}
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
	 *	Creates a new HttpServlet instance (or subclass of it)
	 *	with injected instances of arguments, if any
	 *
	 *	@param	\Collei\Http\HttpServlet	$request
	 *	@param	\Collei\Http\Request		$request
	 *	@return	\Collei\Http\HttpServlet
	 */
	private static function makeServlet2(Request $request)
	{
		$ref_class = new ReflectionClass(get_called_class());
		$ref_construct = $ref_class->getConstructor();
		$ref_params = $ref_construct->getParameters();
		// basic types and default values
		$atomic = [
			'array' => [],
			'callable' => (function(...$params){ return false; }),
			'bool' => false,
			'float' => 0.0,
			'int' => 0,
			'string' => '',
		];
		// housing parameters here
		$parameters = [];
		//
		foreach ($ref_params as $ref_parm) {
			$name = $ref_parm->getName();
			$type = $ref_parm->getType();

			if (!is_null($type))
			{
				$type = $type->getName();
			}
			else
			{
				$type = '';
			}

			if ($type === Request::class)
			{
				$parameters[] = $request;
			}
			elseif (is_subclass_of($type, Request::class))
			{
				if ($type === FileUploadRequest::class)
				{
					if ($request instanceof FileUploadRequest)
					{
						$parameters[] = $request;
					}
					else
					{
						$parameters[] = FileUploadRequest::capture();
					}
				}
				else
				{
					$parameters[] = $type::capture();
				}
			}
			elseif (is_subclass_of($type, Service::class))
			{
				$parameters[] = $type::make();
			}
			elseif (is_a($type, Capturable::class, true))
			{
				$parameters[] = $type::capture();
			}
			elseif (is_a($type, Makeable::class, true))
			{
				$parameters[] = $type::make();
			}
			elseif ($request->hasParameter($name))
			{
				$parameters[] = $request->getParameter($name);
			}
			elseif ($ref_parm->isOptional())
			{
				$parameters[] = $ref_parm->getDefaultValue();
			}
			elseif (array_key_exists($type, $atomic))
			{
				$parameters[] = $atomic[$type];
			}
			else
			{
				$parameters[] = null;
				//
				logerror(
					'Servlet method: mandatory not present',
					"Missing argument $name of type $type not present in the request "
				);
			}
		}
		//
		return $ref_class->newInstance(...$parameters);
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


