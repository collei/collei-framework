<?php
namespace Collei\Events;

use Collei\Events\EventInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Interface for all events with arbitrary data.
 */
interface DataEventInterface extends EventInterface
{
	/**
	 *	Checks for data existence.
	 *
	 *	@param	string	$name
	 *	@return	bool
	 */
	public function has(string $name): bool;

	/**
	 *	Retrieves data, or the provided default.
	 *
	 *	@param	string	$name
	 *	@param	mixed	$default = null
	 *	@return	mixed
	 */
	public function get(string $name, $default = null);

	/**
	 *	Stores data.
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value
	 *	@return	bool
	 */
	public function set(string $name, $value);

	/**
	 *	Stores data.
	 *
	 *	@param	string	$name
	 *	@return	void
	 */
	public function remove(string $name);

}

