<?php
namespace App\Filters;

use Collei\Http\Filters\Filter;

class CsrfPostRequestFilter extends Filter
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
		return [
			'GET *',
			'HEAD *',
		];
	}

	public function filter()
	{
		$form_token = $this->request->getParameter('_token');
		$sess_token = $this->session->csrf;

		//logit(__METHOD__, print_r([$form_token, $sess_token, $this->session], true));

		if ($form_token === $sess_token)
		{
			$this->session->regenerateToken();
			//
			return true;
		}

		$error_desc = 'CSRF Protection is enabled. This request was not successful as a result of CSRF attempt or a XSS attack being blocked.';

		logerror('CSRF attempt !!!', print_r([$error_desc, [$form_token, $sess_token, $this->session]], true));

		// trying void undue csrf notices... 
		//$this->session->destroy(true);

		$this->session->flash('error', $error_desc);

		redirect('/sites/logout');

		//return view('index');
	}
	
}

