<?php
namespace Collei\Console\Output;

use Collei\Console\Co;
use Collei\Console\Output\OutputFormatInterface;
use Collei\Console\Output\OutputStyleParser;

/**
 *	This class represents a console format for writing
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-11-26		
 */
class OutputFormat implements OutputFormatInterface
{
	/**
	 *	@var	string	$name
	 */
	protected $name = '';

	/**
	 *	@var	string	$foreground
	 */
	protected $foreground = '';

	/**
	 *	@var	string	$background
	 */
	protected $background = '';

	/**
	 *	@var	array	$effects
	 */
	protected $effects = [];

	/**
	 *	Builds a new instance of OutputFormat with its settings
	 *
	 *	@param	string	$name		name of the format
	 *	@param	string	$fgColor	foreground color (either color name, int or hex format)	
	 *	@param	string	$bgColor	background color (either color name, int or hex format)	
	 *	@param	array	$effects	one or more of these effects (bold, underline, reversed)	
	 */	
	public function __construct(string $name, string $fgColor, string $bgColor, array $effects = [])
	{
		$this->name = $name;
		$this->foreground = $fgColor;
		$this->background = $bgColor;
		$this->effects = $effects;
	}

	/**
	 *	Returns the style name
	 *
	 *	@return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *	Outputs $text according to the style definitions
	 *
	 *	@param	string	$content
	 *	@return	void
	 */
	public function write(string $content)
	{
		Co::write(
			$content,
			$this->foreground,
			$this->background,
			$this->effects
		);
	}

	/**
	 *	Outputs $text according to the style definitions, starting
	 *	from ($x,$y) screen coordinates
	 *
	 *	@param	string	$content
	 *	@param	int		$x			console column position	
	 *	@param	int		$y			console row position
	 *	@return	void
	 */
	public function writeTo(string $content, int $x, int $y)
	{
		Co::writeTo(
			$x,
			$y,
			$content,
			$this->foreground,
			$this->background,
			$this->effects
		);
	}

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
		string $content,
		int $left,
		int $top,
		int $right,
		int $bottom
	) {
		Co::writeInRect(
			$left,
			$top,
			$right,
			$bottom,
			$content,
			$this->foreground,
			$this->background,
			$this->effects
		);
	}

	/**
	 *	Outputs $text according to the style definitions, plus a newline
	 *
	 *	@param	string	$content
	 */
	public function writeln(string $content)
	{
		$this->write($content);

		Co::write("\r\n");
	}

	/**
	 *	Builds a new instance of OutputFormat from a tag spec like
	 *		<fg=color;bg=color;options=bold,underline,reverse>
	 *	with the same values as the ones accepted by the constructor
	 *
	 *	@param	string	$name		name of the format
	 *	@param	string	$spec		a valid tag	as described above
	 *	@return	\Collei\Console\Output\OutputFormat
	 */	
	public static function fromTag(string $name, string $spec)
	{
		$spec = trim($spec);
		$params = [];

		if (OutputStyleParser::parse($spec, $params))
		{
			if (in_array($params['type'], ['start','full']))
			{
				return new self(
					$name,
					$params['fg'] ?? 'default',
					$params['bg'] ?? 'default',
					$params['options'] ?? []
				);
			}
		}
		else
		{
			return new self($name, 'default', 'default', []);
		}
	}


}
