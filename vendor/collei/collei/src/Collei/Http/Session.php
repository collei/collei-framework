<?php
namespace Collei\Http;

use Collei\Support\Str;

/**
 *	@author	linblow AT hotmail DOT fr
 *	@since	2011-02-16 05:06
 *	@see	https://www.php.net/manual/pt_BR/function.session-start.php#102460 <accessed 2021-10-31 GMT-3>
 *
 *	manages user Sessions
 */
class Session
{
	/**
	 *	@var	string	$token
	 *	@var	string	$csrf
	 */
	const SESSION_STARTED = TRUE;
	const SESSION_NOT_STARTED = FALSE;

	// Session options
	private static $options = [
		'cookie_lifetime' => 86400
	];

	// The state of the session
	private $sessionState = self::SESSION_NOT_STARTED;

	// THE only instance of the class
	private static $instance = null;

	private function __construct()
	{
		//$this->csrf_token = $_SESSION['_token'] ?? Str::random();
	}

	/**
	 *	Returns THE instance of 'Session'.
	 *	The session is automatically initialized if it wasn't.
	 *
	 *	@return	Collei\Http\Session
	 */
	public static function capture()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self;
		}
		//
		self::$instance->startSession();
		//
		return self::$instance;
	}

	/**
	 *	(Re)starts the session.
	 *
	 *	@return	bool	TRUE if the session has been initialized,
	 *					FALSE otherwise.
	 */
	public function startSession()
	{
		if ($this->sessionState == self::SESSION_NOT_STARTED) {
			if ($this->sessionState = session_start(self::$options)) {
				$this->regenerateToken();
			}
		}
		//
		return $this->sessionState;
	}

	/**
	 *	Regenerates token
	 *
	 *	@return	void
	 */
	public function regenerateToken()
	{
		if (!isset($_SESSION['_token'])) {
			$_SESSION['_token'] = Str::random();
		}
	}

	/**
	 *	Erases discardables
	 *
	 *	@return	void
	 */
	public function eraseDiscardables()
	{
	}

	/**
	 *	Stores datum in the session.
	 *	Example: $instance->foo = 'bar';
	 *
	 *	@param	name	Name of the datum.
	 *	@param	value	Your datum.
	 *	@return	void
	 */
	public function __set(string $name, $value)
	{
		if (!in_array($name, ['_token','_flash','_published'])) {
			$_SESSION[$name] = $value;
		}
	}

	/**
	 *	Gets datum from the session.
	 *	Example: echo $instance->foo;
	 *
	 *	@param	name	Name of the datas to get.
	 *	@return	mixed	Datum stored in session.
	 */
	public function __get(string $name)
	{
		if ($name === 'token' || $name === 'csrf') {
			return $_SESSION['_token'];
		}
		if (array_key_exists($name, $_SESSION)) {
			return $_SESSION[$name];
		}
	}

	public function __isset($name)
	{
		return array_key_exists($name, $_SESSION);
	}

	public function __unset($name)
	{
		unset($_SESSION[$name]);
	}

	public function __debugInfo()
	{
		$internals = [ 'sessionState' => $this->sessionState ];
		$values = $_SESSION;
		//
		return array_merge($internals, $values);
	}

	/**
	 *	Checks if a given $value/$subValue exists
	 *
	 *	$name is mandatory
	 *	$subname is optional.
	 *	If $_SESSION[$name] exists and is an array:
	 *		If $subValue is omitted, returns true.
	 *		If $subValue is given, returns true if it exists, false otherwise
	 *	If $_SESSION[$name] is not array:
	 *		Returns true if it exists, false otherwise.
	 *		In this case, $subName has no effect and it is ignored.
	 *
	 *	@param	$name		string	the name of session variable
	 *	@param	$subName	string	a specific index in such session variable (if array)
	 *	@return	bool	
	 */
	public function has(string $name, string $subName = null)
	{
		if (array_key_exists($name, $_SESSION)) {
			if (is_array($_SESSION[$name])) {
				if (!is_null($subName)) {
					return array_key_exists($subName, $_SESSION[$name]);
				} else {
					return true;
				}
			} else {
				return true;
			}
		}
		//
		return false;
	}

	/**
	 *	Returns a value or a set of values
	 *
	 *	$name is mandatory
	 *	$subName is optional.
	 *	If $_SESSION[$name] is an array:
	 *		If $subValue is omitted or it does not exist, returns $_SESSION[$name].
	 *		Otherwise, returns the value of $_SESSION[$name][$subName]
	 *	If $_SESSION[$name] is not array: $subName has no effect and it is ignored.
	 *
	 *	@param	$name		string	the name of session variable
	 *	@param	$subName	string	a specific index in such session variable (if array)
	 *	@return	string|array	if $name holds an array value
	 */
	public function get(string $name, string $subName = null)
	{
		if (array_key_exists($name, $_SESSION)) {
			if (is_array($_SESSION[$name])) {
				if (is_null($subName)) {
					return $_SESSION[$name];
				} elseif (array_key_exists($subName, $_SESSION[$name])) {
					return $_SESSION[$name][$subName];
				}
			} else {
				return $_SESSION[$name];
			}
		}
	}

	/**
	 *	Sets a value to a $name and $subName
	 *
	 *	both $name and $subName are mandatory
	 *	If and only if $_SESSION[$name] is an array: sets the value
	 *
	 *	@param	$name		string	the name of session variable
	 *	@param	$subName	string	a specific index in such session variable
	 *	@param	$value		mixed	value to set
	 *	@return	bool	true if succeeded, false otherwise
	 */
	public function set(string $name, string $subName, $value)
	{
		if (array_key_exists($name, $_SESSION ?? [])) {
			if (is_array($_SESSION[$name])) {
				$_SESSION[$name][$subName] = $value;
				return true;
			} else {
				$_SESSION[$name] = [ $subName => $value ];
				return true;
			}
		} else {
			$_SESSION[$name] = [ $subName => $value ];
		}
		//
		return false;
	}

	/**
	 *	Removes a value associated to the $name and $subName
	 *
	 *	both $name and $subName are mandatory
	 *	If and only if $_SESSION[$name] is an array: removes the value (if exists)
	 *
	 *	@param	$name		string	the name of session variable
	 *	@param	$subName	string	a specific index in such session variable
	 *	@return	bool		true if succeeded, false otherwise
	 */
	public function remove(string $name, string $subName)
	{
		if (array_key_exists($name, $_SESSION)) {
			if (is_array($_SESSION[$name])) {
				unset($_SESSION[$name][$subName]);
				return true;
			}
		}
		//
		return false;
	}

	/**
	 *	Set a message to last just for the next session
	 *
	 *	@param	string	$name		name of the message
	 *	@param	mixed	$content	content of the message
	 */
	public function flash(string $name, $content)
	{
		$this->set('_flash', $name, $content);
	}

	/**
	 *	Set a variable to be published into views
	 *
	 *	@param	string	$name		name of the variable
	 *	@param	mixed	$value		value of the variable
	 */
	public function publish(string $name, $value)
	{
		$this->set('_published', $name, $value);
	}

	/**
	 *	Return an array of flash messages and erases them from the session
	 *
	 *	@return	array
	 */
	public function flashed()
	{
		$flashed = $this->get('_flash');
		//
		if (is_null($flashed)) {
			return [];
		}
		//
		unset($_SESSION['_flash']);
		//
		return is_array($flashed) ? $flashed : [ 'flashed' => $flashed ];
	}

	/**
	 *	Return an array of the published variables
	 *
	 *	@return	array
	 */
	public function published()
	{
		$published = $this->get('_published');
		//
		if (is_null($published)) {
			return [];
		}
		//
		return is_array($published)
			? $published
			: [ 'published' => $published ];
	}

	/**
	 *	Destroys the current session.
	 *
	 *	@param	bool	$removeCookies	True to remove the session cookies
	 *	@return	bool	TRUE is session has been deleted, else FALSE.
	 */
	public function destroy(bool $removeCookies = false)
	{
		if ($this->sessionState == self::SESSION_STARTED) {
			$this->sessionState = !session_destroy();
			unset($_SESSION);
			//
			if ($removeCookies) foreach ($_COOKIE as $n => $v) {
				setcookie($n, '', time() - 43200);
			}
			//		
			return !$this->sessionState;
		}
		//
		return FALSE;
	}
}

/*

If you want to handle sessions with a class, I wrote this little class:
	(COLLEI NOTE: the class is declared above)


	Examples:
**

// We get the instance
$data = Session::getInstance();

// Let's store datas in the session
$data->nickname = 'Someone';
$data->age = 18;

// Let's display datas
printf('<p>My name is %s and I\'m %d years old.</p>' , $data->nickname , $data->age);

/*
	It will display:

	Array
	(
		[nickname] => Someone
		[age] => 18
	)
**

printf('<pre>%s</pre>' , print_r($_SESSION , TRUE));

// TRUE
var_dump(isset($data->nickname));

// We destroy the session
$data->destroy();

// FALSE
var_dump(isset($data->nickname));

I prefer using this class instead of using directly the array $_SESSION.

*/
