<?php

use Collei\Http\Routing\Router;
//use Collei\Console\Routing\CommandEntries;

use App\Servlets\LogonServlet;
use App\Servlets\IndexPageServlet;
use App\Servlets\UserRegisterServlet;

use App\Servlets\AdminPanelServlet;
use App\Servlets\UserManagerServlet;
use App\Servlets\RoleManagerServlet;
use App\Servlets\SiteManagerServlet;

/*
 *	route entries
 */

/*
for "/home" do IndexPageServlet::index as "plat-index"
for "/logon" do LogonServlet::logon as "plat-logon"
*/

Router::any('/home', IndexPageServlet::class, 'index')
	->name('plat-index');

Router::get('/logon', LogonServlet::class, 'logon')
	->name('plat-logon');

Router::post('/mfa-logon', LogonServlet::class, 'logonConfirm')
	->name('plat-logon-confirm');

Router::post('/logon', LogonServlet::class, 'logonAction');

Router::get('/logout', LogonServlet::class)
	->name('plat-logout')
	->servletMethod('logout');

Router::controller(UserRegisterServlet::class)->group(function() {
	Router::get('/register', 'register')->name('plat-register');
	Router::post('/register', 'registerAction');
	Router::get('/mypanel', 'userPanel')->name('plat-userpanel');
	Router::get('/mfa-register', 'mfaRegisterStart')
		->name('plat-mfa-register');
	Router::post('/mfa-register', 'mfaRegisterComplete')
		->name('plat-mfa-register-complete');
});

/*
Router::get('/register', UserRegisterServlet::class)
	->name('plat-register')
	->servletMethod('register');

Router::post('/register', UserRegisterServlet::class)
	->servletMethod('registerAction');

Router::get('/mypanel', UserRegisterServlet::class, 'userPanel')
	->name('plat-userpanel');

Router::get('/mfa-register', UserRegisterServlet::class, 'mfaRegisterStart')
	->name('plat-mfa-register');

Router::post('/mfa-register', UserRegisterServlet::class, 'mfaRegisterComplete')
	->name('plat-mfa-register-complete');
*/

/*
 *	admin panel
 */

/*
in "/admin"
	for "/home" do AdminPanelServlet::index as "plat-index"

	within UserManagerServlet::class
		for "/users" do :index as "plat-adm-users"
	without
out

*/


Router::prefix('admin')->group(function() {

	Router::get('home', AdminPanelServlet::class, 'index')
		->name('plat-adm-panel');

	/*
	 *	user manager
	 */
	Router::controller(UserManagerServlet::class)->group(function() {
		Router::get('users', 'index')->name('plat-adm-users');

		Router::get('users/{user_id}', 'detail');
		Router::post('users/{user_id}/sites/{site_id}/roles/{role_id}', 'setRoleOnSite');
		Router::post('users/{user_id}/changepassword', 'setNewPassword');
		Router::post('users/{user_id}/removemfa', 'removeMFA');
	});

	/*
	 *	roles manager
	 */
	Router::controller(RoleManagerServlet::class)->group(function() {
		Router::get('roles', 'index')->name('plat-adm-roles');

		Router::post('roles', 'create');
		Router::get('roles/{role_id}', 'detail');
		Router::post('roles/{role_id}', 'modify');
	});

	/*
	 *	sites manager
	 */
	Router::controller(SiteManagerServlet::class)->group(function() {
		Router::get('siteman', 'index')
			->name('plat-adm-sites');

		Router::post('siteman', 'create');
		Router::get('siteman/{site_id}', 'detail')->name('plat-adm-siteman-detail');
		Router::post('siteman/{site_id}', 'modify');

		Router::get('siteman/{engine}/files', 'engineView')
			->name('plat-adm-site-engine-view');

		Router::put('siteman/{engine}/files/add', 'engineAddFile')
			->name('plat-adm-site-engine-addfile');
	});

});

/*
 *	default handler
 */

Router::default(IndexPageServlet::class, 'pageNotFound');


