<?php
namespace Collei\Http;

use ReflectionClass;
use ReflectionMethod;
use Collei\App\App;
use Collei\Http\Routing\Route;
use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Http\HttpServlet;
use Collei\App\Servlets\Servlet;
use Collei\App\Services\Service;
use Collei\Http\Uploaders\FileUploadRequest;
use Collei\Support\Arr;

/**
 *	Encapsulates the servlet caller
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-05-xx
 */
class ServletDispatcher
{
	/**
	 *	@var \Collei\App\App $app
	 */
	private $app;

	/**
	 *	Retrieve the available parameters from the method  
	 *
	 *	@param	\ReflectionMethod	$refMethod
	 *	@param	\Collei\Http\Request	$request
	 *	@return	mixed
	 */
	private function getMethodParameters(ReflectionMethod $refMethod, Request $request)
	{
		$ref_parm_arr = $refMethod->getParameters();
		$parameters = array();
		$atomic = ['array','callable','bool','float','int','string'];
		//
		foreach ($ref_parm_arr as $ref_parm) {
			$name = $ref_parm->getName();
			$type = $ref_parm->getType();
			//
			if (!is_null($type)) {
				$type = $type->getName();
			} else {
				$type = '';
			}
			//
			if ($type === Request::class) {
				$parameters[$name] = $request;
			} elseif (
				!in_array($type, $atomic) && is_subclass_of($type, Request::class)
			) {
				if ($type === FileUploadRequest::class) {
					if ($request instanceof FileUploadRequest) {
						$parameters[$name] = $request;
					} else {
						$parameters[$name] = FileUploadRequest::capture();
					}
				} else {
					$parameters[$name] = $type::capture();
				}
			} elseif (
				!in_array($type, $atomic) && is_subclass_of($type, Service::class)
			) {
				$parameters[$name] = $type::make();
			} elseif ($name == 'request') {
				$parameters['request'] = $request;
			} elseif ($request->hasParameter($name)) {
				$parameters[$name] = $request->getParameter($name);
			} elseif ($ref_parm->isOptional()) {
				$parameters[$name] = $ref_parm->getDefaultValue();
			} else {
				$parameters[$name] = null;
				logerror('Servlet method: mandatory not present', "Missing argument $name of type $type not present in the request ");
			}
		}
		//
		return $parameters;
	}

	/**
	 *	Calls the involved instance method
	 *
	 *	@param	instanceof \Collei\Http\Request	$instance
	 *	@param	string	$method
	 *	@param	\Collei\Http\Request	$request
	 *	@return	mixed
	 */
	private function callInstance($instance, $method, $request)
	{
		if (!is_null($instance)) {
			$ref_class = new ReflectionClass($instance);
			$http_method = strtolower($request->method);
			//
			if ($ref_class->hasMethod($method)) {
				$ref_method = $ref_class->getMethod($method);
				$parameters = $this->getMethodParameters($ref_method, $request);
				//
				return $instance->callAction($method, $parameters);
			} elseif ($ref_class->hasMethod($http_method)) {
				return $instance->callAction($http_method, [$request]);
			} else {
				return $instance->callAction('handle', [$request]);
			}
		} else {
			return new Response();
		}
	}

	/**
	 *	Dispatches the call to be handled
	 *
	 *	@param	string	$servletClass
	 *	@param	\Collei\Http\Request	$request
	 *	@return	instanceof \Collei\Http\Servlet
	 */
	private function getServletInstance(string $servletClass, Request $request)
	{
		return $servletClass::make($request);
	}

	/**
	 *	Builds and initializes a servlet instance
	 *
	 *	@param	\Collei\App\App	$app
	 */
	public function __construct(App $app)
	{
		$this->app = $app;
	}

	/**
	 *	Dispatches the call to be handled
	 *
	 *	@param	\Collei\Http\Routing\Route	$route
	 *	@param	\Collei\Http\Request		$request
	 *	@return	mixed
	 */
	public function dispatch(Route $route, Request $request)
	{
		$servlet_class = $route->getServletClass();
		$servlet_method = $route->getServletMethod();
		$instance = $this->getServletInstance($servlet_class, $request);
		//
		return $this->callInstance($instance, $servlet_method, $request);
	}

}


