<?php
namespace App\Filters;

use Collei\Http\Filters\Filter;
use Collei\Http\Session;

class PlatStatsInterceptorFilter extends Filter
{

	/**
	 *	allows ignore this filter for certain requests
	 *
	 *	@return array 	List of rules in format '<verb> <uri>'. 
	 *					<verb> must be a valid HTTP verb or an asterisk.
	 *					<uri> must be a valid route URI or an asterisk.
	 *					Both are optional. An '' (empty) line
	 *					or a '* *' (astyerisk + space + asterisk) line
	 *					disables the filter
	 */
	public function except()
	{
		return [];
	}

	public function filter()
	{
		//$target = $this->request->routeSite;
		//$visitime = date('Y-m-d H:i:s');

		$sess = Session::capture();

		//logit(__METHOD__, print_r(['csrf' => $sess->csrf], true));

		return true;
	}
	
}

