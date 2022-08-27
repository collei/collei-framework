<?php
namespace App\Filters;

use Collei\Http\Filters\Filter;

class AuthFilter extends Filter
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
			'* /home',
			'* /mfa-logon',
			'* /logon',
			'* /logout',
			'* /register',
		];
	}

	public function filter()
	{
		$sessao = $this->session;

		if (isset($sessao->user))
		{
			return true;
		}

		// trying void undue csrf notices... 
		$this->session->destroy(true);
		$this->session->flash('message', 'You must be logged in to view that page.');

		redirect('/sites/logon');
	}
		
}

