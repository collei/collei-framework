<?php
namespace Collei\Console\Output;

/**
 *	This interface represents a console format for writing
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-11-26		
 */
interface OutputFormatInterface
{

	/**
	 *	Outputs $text according to the style definitions
	 *
	 *	@param	string	$content
	 *	@return	void
	 */
	public function write(string $content);

	/**
	 *	Outputs $text according to the style definitions, starting
	 *	from ($x,$y) screen coordinates
	 *
	 *	@param	string	$content
	 *	@param	int		$x			console column position	
	 *	@param	int		$y			console row position
	 *	@return	void
	 */
	public function writeTo(string $content, int $x, int $y);

	/**
	 *	Outputs $text according to the style definitions,
	 *	into the defined rect
	 *
	 *	@param	string	$content
	 *	@param	int		$left		console first column position	
	 *	@param	int		$top		console first row position
	 *	@param	int		$right		console last column position
	 *	@param	int		$bottom		console last row position
	 *	@return	void
	 */
	public function writeInRect(
		string $content, int $left, int $top, int $right, int $bottom
	);

	/**
	 *	Outputs $text according to the style definitions, plus a newline
	 *
	 *	@param	string	$content
	 */
	public function writeln(string $content);

}
