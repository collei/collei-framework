<?php
namespace Collei\Auth\Models;

use Collei\Auth\Authenticat;
use Collei\Database\Query\DB;
use Collei\Database\Yanfei\Model;
use Collei\Utils\Str;

/**
 *	Encapsulates methods for authentication
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
abstract class AuthUser extends Model
{
	/**
	 *	@var string $name
	 */
	protected $usernameField = 'name';

	/**
	 *	@var string $emailField
	 */
	protected $emailField = 'email';

	/**
	 *	@var string $passwordField
	 */
	protected $passwordField = 'password';

	/**
	 *	Returns the name of the username field
	 *
	 *	@return	string
	 */
	private function getUsernameField()
	{
		return $this->usernameField;
	}

	/**
	 *	Returns the name of the email field
	 *
	 *	@return	string
	 */
	private function getEmailField()
	{
		return $this->emailField;
	}

	/**
	 *	Returns the name of the password field
	 *
	 *	@return	string
	 */
	private function getPasswordField()
	{
		return $this->passwordField;
	}

	/**
	 *	Checks if the given name is the name of the username field
	 *
	 *	@param	string	$name
	 *	@return	bool
	 */
	protected function isUsernameField(string $name)
	{
		return (Str::toSnake($name) === $this->getUsernameField());
	}

	/**
	 *	Checks if the given name is the name of the email field
	 *
	 *	@param	string	$name
	 *	@return	bool
	 */
	protected function isEmailField(string $name)
	{
		return (Str::toSnake($name) === $this->getEmailField());
	}

	/**
	 *	Checks if the given name is the name of the password field
	 *
	 *	@param	string	$name
	 *	@return	bool
	 */
	protected function isPasswordField(string $name)
	{
		return (Str::toSnake($name) === $this->getPasswordField());
	}

	/**
	 *	Returns the encoded version of the password
	 *
	 *	@param	string	$password
	 *	@return	string
	 */
	protected function encodePassword(string $password)
	{
		return Authenticat::passwordHash($password);
	}

	/**
	 *	Performs password check
	 *
	 *	@param	string	$password
	 *	@return	bool
	 */
	protected function verifyPassword(string $password)
	{
		$hashFieldName = $this->getPasswordField();
		$hash = $this->$hashFieldName;

		return Authenticat::passwordCheck($password, $hash);
	}

	/**
	 *	Changes the password
	 *
	 *	@param	string	$name
	 *	@return	void
	 */
	public function setPassword(string $value)
	{
		$passName = $this->getPasswordField();

		$this->setAttribute($passName, $this->encodePassword($value));
	}

	/**
	 *	Performs the password check
	 *
	 *	@param	string	$password
	 *	@return	bool
	 */
	public function check(string $password)
	{
		return $this->verifyPassword($password);
	}

	/**
	 *	Finds entity by name or email
	 *
	 *	@param	array	$fields
	 *	@return	array
	 */
	protected static function findByNameOrEmail(array $fields)
	{
		$inst = static::new();
		$tableName = $inst->getTable();
		$where = DB::from($tableName)->select('*')->where();
		$first = true;

		foreach ($fields as $n => $v)
		{
			if ($inst->isUsernameField($n) || $inst->isEmailField($n))
			{
				if ($first)
				{
					$first = false;
				}
				else
				{
					$where->or();
				}
				$where->is($n, $v);
			}
		}

		$data = $where->gather();

		return static::fillModelList($data, true, static::class);
	}

	/**
	 *	Finds a user either by username or email and performs authentication 
	 *
	 *	@param	array	$fields
	 *	@param	string	$password
	 *	@return	bool
	 */
	public static function authenticate(array $fields, string $password)
	{
		$candidates = static::findByNameOrEmail($fields);

		foreach ($candidates as $candidate)
		{
			if ($candidate->check($password))
			{
				return $candidate;
			}
		}

		return null;
	}

}


