<?php
namespace App\Filters;

use Collei\Http\Filters\Filter;

use App\Models\Site;

class AvailabilityInterceptorFilter extends Filter
{

	/**
	 *	allows ignore this filter for certain requests
	 *
	 *	@return array 	List of rules in format '<verb> <uri>'. 
	 *					<verb> must be a valid HTTP verb or an asterisk.
	 *					<uri> must be a valid route URI or an asterisk.
	 *					Both are optional. An empty line or a '* *' (astyerisk + space + asterisk) line disables the filter
	 */
	public function except()
	{
		return [
			'* /logon',
			'* /mfa-logon',
		];
	}

	public function filter()
	{
		$target = $this->request->routeSite;

		if ($target != PLAT_NAME)
		{
			$site = Site::from(['name' => $target]);

			if (empty($site))
			{
				$this->session->flash('error', "Site $target does not exist.");
				return view('index');
			}

			$site = $site->firstResult();
			$available = !$site->isDown;

			if (!$available)
			{
				$this->session->flash('error', "Site <b>$target</b> is unavailable. Please return later.");
				redirect('/sites/home');
			}
		}

		return true;
	}
	
}

