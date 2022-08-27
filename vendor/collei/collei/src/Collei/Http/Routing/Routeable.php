<?php

namespace Collei\Http\Routing;

use Collei\Http\Routing\Route;

/**
 *	Represents a routeable object that may be bound to a given Route
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
interface Routeable
{
	/**
	 *	Binds the given route
	 *
	 *	@param	\Collei\Http\Routing\Route	$route
	 *	@return	void
	 */
	public function bindRoute(Route $route);
}

