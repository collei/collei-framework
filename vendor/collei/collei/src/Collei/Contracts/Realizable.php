<?php
namespace Collei\Contracts;

/**
 *	allow classes to be realized, i.e., to make their state
 *	externally immutable since realize() is called
 *
 *	@author	alarido
 *	@since	2021-07-xx
 */
interface Realizable
{
	/**
	 *	makes the object externally immutable after called
	 *
	 *	@return	void
	 */
	public function realize();

	/** 
	 *	returns true if object is realized, false otherwise
	 *
	 *	@return	bool
	 */
	public function realized();
}


