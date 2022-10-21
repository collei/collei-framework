<?php 
namespace Collei\App\Agents;

use Collei\Basement;
use Collei\App\App;
use Collei\Support\Filesystem\Folder;
use Collei\Support\Str;
use UnexpectedValueException;

/**
 *	Embodies the storage agent, using virtual, relative paths
 *	for retrieval of the absolute ones, imposing referral limit 
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-04-27
 */
class Storage extends Folder
{
	/**
	 *	@var \Collei\App\App $app
	 */
	private $app = null;

	/**
	 *	@var string $site
	 */
	private $site = '';

	/**
	 *	@var string $ground
	 */
	private $ground = '';

	/**
	 *	@var bool $silentMode
	 */
	private $silentMode = false;

	/**
	 *	Initializes the Storage instance and returns reference for
	 *	the current running app instance.
	 *
	 *	@return	\Collei\App\App
	 */
	private function init()
	{
		$this->app = App::getInstance();
		$this->site = $this->app->getSite();
		$this->ground = $this->app->getStorageGround();
		//
		return $this->app;
	}

	/**
	 *	Instantiates a new Storage handler object
	 *
	 *	@return	\Collei\App\Storage
	 */
	public function __construct()
	{
		parent::__construct(
			$this->init()->getStorageGround()
		);
	}

	/**
	 *	Builds a whole tree of folders like my/happy/folder.
	 *	With the $ignoreLastPart = true, you can pass something
	 *	like my/happy/folder/document.txt for creating the
	 *	my/happy/folder part needed to house the document.txt.
	 *	Retruns true if creation is successful, false otherwise.
	 *
	 *	@param	string	$tree
	 *	@param	bool	$ignoreLastPart
	 *	@return	void
	 *	@throws	\UnexpectedValueException
	 */
	public function makeTree(string $tree, bool $ignoreLastPart = false)
	{
		if (
			!Str::startsWith(
				$realTree = $this->realizePath($tree), $this->ground
			)
		) {
			if ($this->silentMode) {
				return false;
			}
			//
			throw new UnexpectedValueException(
				"The path '$tree' violated storage path restriction."
			);
		}
		//
		if ($ignoreLastPart) {
			$tree = dirname($tree);
		}
		//
		return static::create($tree);
	}

	/**
	 *	Builds a whole tree of folders like my/happy/folder.
	 *	With the $ignoreLastPart = true, you can pass something
	 *	like my/happy/folder/document.txt for creating the
	 *	my/happy/folder part needed to house the document.txt.
	 *	Retruns true if creation is successful or if it already exists,
	 *	false otherwise.
	 *
	 *	@param	string	$tree
	 *	@param	bool	$ignoreLastPart
	 *	@return	void
	 *	@throws	\UnexpectedValueException
	 */
	public function makeTreeIfNotExists(string $tree, bool $ignoreLastPart = false)
	{
		$realTree = $this->realizePath($tree);
		//
		if (!Str::startsWith($realTree, $this->ground)) {
			if ($this->silentMode) {
				return false;
			}
			//
			throw new UnexpectedValueException("The path '$tree' violated storage path restriction.");
		}
		//
		if ($ignoreLastPart) {
			$realTree = dirname($realTree);
		}
		//
		return static::createIfNotExists($realTree);
	}

	/**
	 *	Enables or disables silent mode, i.e., whether Exceptions will be
	 *	throw or not by this object. It does not affects any other objects
	 *	used by this one to achieve its goals.
	 *
	 *	@param	bool	$silent
	 *	@return	void
	 */
	public function setSilent(bool $silent)
	{
		$this->silentMode = $silent;
	}

	/**
	 *	@property string $app
	 *	@property string $site
	 *	@property string $ground
	 *	@property string $name
	 *	@property string $path
	 *	@property string $real
	 *	@property string $realpath
	 *	@property string $exists
	 */
	public function __get($name)
	{
		if (in_array($name, ['app','site','ground','silentMode'])) {
			return $this->$name;
		}
		//
		return parent::__get($name);
	}

	/**
	 *	Debug info through var_dump, print_r, and so on
	 *
	 *	@return	array
	 */
	public function __debugInfo()
	{
		return array_merge([
			'site' => $this->site,
			'ground' => $this->ground,
		], parent::__debugInfo());
	}

	/**
	 *	Returns the Storage instance for the cufrrent running app 
	 *
	 *	@return	\Collei\App\Agents\Storage
	 */
	public static function get()
	{
		return new self();
	}

	/**
	 *	Returns a realized version of the given $virtual path,
	 *	relative to the storage ground, even it does not exists.
	 *
	 *	@param	string	$virtual
	 *	@return	string
	 */
	protected function realizePath(string $virtual = '.')
	{
		if ($virtual == '/') {
			return $this->ground;
		}
		//
		if (Str::startsWith($virtual, '/')) {
			return $this->ground . Str::replace('/', DIRECTORY_SEPARATOR, $virtual);
		}
		//
		return static::absolutize($this->ground . DIRECTORY_SEPARATOR . $virtual);
	}

}


