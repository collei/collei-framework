<?php
namespace Collei\Http\Filters;

use InvalidArgumentException;
use Collei\App\App;
use Collei\Utils\Arr;
use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Http\Filters\Filter;

/**
 *	Encapsulates the operation upon a set of filters
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-03-xx
 */
class FilterChain
{
	/**
	 *	@var @static array $chain
	 */
	private static $chain = [];

	/**
	 *	require filter classes
	 *
	 *	@param	string	$filterClass
	 *	@return	bool
	 */
	private static function requireFilter(string $filterClass)
	{
		$app_site = App::getInstance()->getSite();

		if (!is_null($app_site) && $app_site!='')
		{
			require_site_class($app_site, $filterClass);
			return true;
		}
		else
		{
			require_manager_class($filterClass);
			return true;
		}

		return false;
	}

	/**
	 *	List every HTTP verb and URI combinations that should be ignored
	 *	when running the filter chain
	 *
	 *	@param	\Collei\Http\Filters\Filter	$filter
	 *	@param	\Collei\Http\Request	$request
	 *	@return	array
	 */
	private static function fetchIgnored(Filter $filter, Request $request)
	{
		$siteName = $request->routeSite;
		$rootPattern = PLAT_SITES_BASEURL;

		if (($siteName != '') && ($siteName != PLAT_NAME))
		{
			$rootPattern .= '/' . $siteName;
		}

		$ignoredPaths = $filter->except();
		$ignoredRules = [];

		foreach ($ignoredPaths as $rule)
		{
			$parts = [];
			preg_match('#^([A-Za-z]+|\*)?\s*?([^\s]+|\*)?$#', $rule, $parts);

			$ignoredRules[] = [
				'verb'	=> (isset($parts[1]) ? $parts[1] : '*'),
				'uri'	=> (isset($parts[2]) ? $parts[2] : '*')
			];
		}

		foreach ($ignoredRules as $i => $rule)
		{
			if ($rule['uri'] !== '*')
			{
				$p = $rule['uri'];
				if (!str_starts_with($p, $rootPattern))
				{
					$ignoredRules[$i]['uri'] = $rootPattern . $p;
				}
			}
		}

		return $ignoredRules;
	}

	/**
	 *	Verifies if the given filter must be ignored or not
	 *
	 *	@param	\Collei\Http\Filters\Filter	$filter
	 *	@param	\Collei\Http\Request	$request
	 *	@return	bool
	 */
	private static function toIgnore(Filter $filter, Request $request)
	{
		$ignored_rules = self::fetchIgnored($filter, $request);
		$request_verb = $request->method;

		foreach ($ignored_rules as $rule)
		{
			if (($rule['verb'] === '*' || $request_verb === $rule['verb'])
				&& ($rule['uri'] === '*' || $request->isRoutePath($rule['uri'])))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 *	Adds a filter to the chain by its class name
	 *
	 *	@param	string	$filterClass
	 *	@param	int		$priority
	 *	@return	void
	 */
	public static function add(string $filterClass, int $priority = null)
	{
		if (!self::requireFilter($filterClass))
		{
			throw new InvalidArgumentException('Class ' . $filterClass . ' not found.');
		}

		if (is_subclass_of($filterClass, Filter::class))
		{
			if (!is_null($priority))
			{
				self::$chain = Arr::insert($filterClass, self::$chain, $priority);
			}
			else
			{
				self::$chain[] = $filterClass;
			}
		}
		else
		{
			throw new InvalidArgumentException('Class ' . $filterClass . ' must extend ' . Filter::class . ' class.');
		}
	}

	/**
	 *	Runs the filter chain, returns whether the request conformed to all filters and,
	 *	if not, returns the result by argument reference
	 *
	 *	@param	\Collei\Http\Request	$request
	 *	@param	\Collei\Http\Response	$response
	 *	@param	\Collei\Http\Filters\Filter	&$failedFilter
	 *	@return	bool
	 */
	public static function run(Request $request, Response $response, Filter &$failedFilter = null)
	{
		$failedFilter = null;

		foreach (self::$chain as $piece)
		{
			$filterClass = $piece;
			$filterInstance = new $filterClass($request, $response);

			if (!self::toIgnore($filterInstance, $request))
			{
				$filterResult = $filterInstance->filter();

				if ($filterResult !== true)
				{
					$failedFilter = $filterInstance;
					return $filterResult;
				}
			}

			$filterInstance = null;
		}

		return true;
	}

	/**
	 *	Runs every filter through the given $closure
	 *
	 *	@param	\Closure	$closure
	 *	@return	bool
	 */
	public static function runEach(Closure $closure)
	{
		foreach (self::$chain as $piece)
		{
			if (!$closure($piece))
			{
				return false;
			}
		}

		return true;
	}

}


