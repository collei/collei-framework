<?php
namespace Collei\Events;

use Collei\Utils\Collections\Properties;

use Collei\Events\Event;
use Collei\Events\DataEventInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Parent class for events with arbitrary data.
 */
class DataEvent extends Event implements DataEventInterface
{
	/**
	 *	@var \Collei\Utils\Collections\Properties $data
	 */
	private $data = null;

	/**
	 *	Checks for data existence.
	 *
	 *	@param	string	$name
	 *	@return	bool
	 */
	public function has(string $name): bool
	{
		return $this->data->has($name);
	}

	/**
	 *	Retrieves data, or the provided default.
	 *
	 *	@param	string	$name
	 *	@param	mixed	$default = null
	 *	@return	mixed
	 */
	public function get(string $name, $default = null)
	{
		return $this->data->get($name, $default);
	}

	/**
	 *	Stores data.
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value
	 *	@return	bool
	 */
	public function set(string $name, $value)
	{
		return $this->data->has($name);
	}

	/**
	 *	Stores data.
	 *
	 *	@param	string	$name
	 *	@return	void
	 */
	public function remove(string $name)
	{
		$this->data->remove($name);
	}

}

