<?php
namespace App\Servlets;

use Collei\Http\HttpServlet;
use Collei\Http\Request;
use Collei\Http\Session;

use App\Models\User;

use App\Services\GoogleAuthService;

class LogonServlet extends HttpServlet
{
	private $google;

	private function doLogonSuccess($user)
	{
		$this->session->uid = $user->id;
		$this->session->user = $user;
		$this->session->type = ($user->name == 'Raiden' ? 'admin' : 'user');

		$this->session->publish('user', $user);

		$this->session->flash('message', 'Logon efetuado com sucesso.');
	}

	public function __construct(Request $request, GoogleAuthService $goo)
	{
		parent::__construct($request);

		$this->google = $goo;
	}

	public function logon()
	{
		if (auth())
		{
			redirect('/sites/home');
		}

		return view('user.logon');
	}

	public function logout()
	{
		$this->session->destroy(true);

		redirect('/sites/home');
	}

	public function logonAction()
	{
		$name = $this->request->username;
		$password = $this->request->password;

		$user = User::authenticate([ 'name' => $name ], $password);

		if (is_null($user))
		{
			$this->session->flash('message', 'Usuário ou senha incorretos');
			redirect('/sites/logon');
		}

		if ($user->mfaEnabled == true)
		{
			//logit(__METHOD__, print_r(['user'=>[$user->id ?? 0,$name,$password],'secret'=>$user->mfaSecret], true));

			$this->session->set('mfa_logon', 'user_id', $user->id);
			$this->session->set('mfa_logon', 'user_secret', $user->mfaSecret);

			return view('user.mfa.logon-confirm', [
				'user' => $user
			]);
		}

		$this->doLogonSuccess($user);
		redirect('/sites/home');
	}

	public function logonConfirm()
	{
		$uid = $this->session->get('mfa_logon', 'user_id');
		$secret = $this->session->get('mfa_logon', 'user_secret');
		$code = ($this->request->confirmcode ?? 0);
		$user = User::fromId($uid);

		//logit(__METHOD__, print_r(['user'=>$uid ?? 0,'secret'=>$secret,'code'=>$code], true));

		if (!$this->google->verify($secret, $code))
		{
			$this->session->flash('message', 'Invalid code. Please try again.');
			redirect('/sites/logon');
		}

		$this->doLogonSuccess($user);
		redirect('/sites/home');
	}

}
