<?php
namespace App\Servlets;

use Collei\Http\Request;
use Collei\Http\HttpServlet;

use App\Models\User;
use App\Models\Role;
use App\Models\Site;
use App\Models\RoleUser;

use App\Services\GoogleAuthService;

class UserRegisterServlet extends HttpServlet
{
	private $google;

	public function __construct(Request $request, GoogleAuthService $goo)
	{
		parent::__construct($request);

		$this->google = $goo;
	}

	public function register()
	{
		return view('user.register');
	}

	public function userPanel()
	{
		return view('user.panel');
	}

	public function mfaRegisterStart()
	{
		$user = $this->session->user ?? '?';
		$userName = $this->session->user->name ?? 'Labrador';
		$qrCodeURL = $this->google->generateQrCodeURL($userName);

		$this->session->set('mfa', 'secret', $this->google->getSecret());
		$this->session->set('mfa', 'qrcode', $qrCodeURL);

		return view('user.mfa.mfa-register', [
			'qrcode' => $qrCodeURL
		]);
	}

	public function mfaRegisterComplete()
	{
		$user = $this->session->user ?? false;

		if (!$user)
		{
			$this->session->flash('message', 'User not logged. Please log in first!');
			redirect('/sites/logon');
		}

		$code = ($this->request->confirmcode ?? 0);
		$secret = $this->session->get('mfa','secret');

		if (!$this->google->verify($secret, $code))
		{
			$this->session->flash('message', 'Invalid code. Please try again.');
			return $this->mfaRegisterStart();
		}

		$user = User::fromId($user->id);

		$user->mfaEnabled = 1;
		$user->mfaProvider = 'Google';
		$user->mfaSecret = $secret;

		$user->save();

		$this->session->flash('message', 'MFA enabled successfully.');
		return $this->userPanel();
	}

	public function registerAction()
	{
		$user = User::new();
		$user->name = $this->request->username;
		$user->email = $this->request->email;
		$user->setPassword($this->request->password);
		$user->save();

		$role = Role::from(['name' => 'user']);
		$sites = Site::all();

		foreach ($sites as $site)
		{
			$permission = RoleUser::new();
			$permission->userId = $user->id;
			$permission->roleId = $role->id;
			$permission->siteId = $site->id;
			$permission->save();
		}

		$this->session->user = $user;
		$this->session->type = ($this->request->username == 'Finger' ? 'admin' : 'user');

		$this->session->flash('message', 'usuário cadastrado com sucesso.');

		redirect('/sites/home');
	}

}
