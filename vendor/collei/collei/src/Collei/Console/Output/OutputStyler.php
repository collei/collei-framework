<?php
namespace Collei\Console\Output;

use Collei\Console\Co;
use Collei\Console\Output\OutputFormat;
use Collei\Console\Output\OutputFormatInterface;
use Collei\Console\Output\OutputStyleParser;
use Collei\Console\Output\OutputStylerInterface;
use InvalidArgumentException;

/**
 *	Encapsulates a set of output styles
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10
 */
class OutputStyler implements OutputStylerInterface
{
	/**
	 *	@var array of \Collei\Console\Output\OutputFormatInterface
	 */
	private $styles;

	/**
	 *	@var \Collei\Console\Output\OutputFormatInterface
	 */
	private $defaultStyle;

	/**
	 *	@var bool
	 */
	private $silent;

	/**
	 *	Buils a new instance of the class
	 *
	 */
	public function __construct()
	{
		$this->styles = [];

		$this->defaultStyle = new OutputFormat('default', 'default', 'default', []);
	}

	/**
	 *	Returns a style from its name, or the default one if not found.
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Console\Output\OutputFormatInterface
	 */
	public function getStyle(string $name)
	{
		if (array_key_exists($name, $this->styles))
		{
			return $this->styles[$name];
		}
		//
		return $this->defaultStyle;
	}

	/**
	 *	Sets a style from a OutputFormatInterface instance or a tag.
	 *
	 *	@param	string	$name
	 *	@param	string|\Collei\Console\Output\OutputFormatInterface $outputStyle
	 *	@return	void
	 */
	public function setStyle(string $name, $outputStyle)
	{
		if ($outputStyle instanceof OutputFormatInterface)
		{
			$this->styles[$name] = $outputStyle;
		}
		elseif (!is_array($outputStyle) && !is_object($outputStyle))
		{
			$this->styles[$name] = OutputFormat::fromTag($name, (string)$outputStyle);
		}
	}

	/**
	 *	Make the output silent, i.e, supress all output 
	 *
	 *	@param	bool	$silent
	 *	@return	void
	 */
	public function setSilent(bool $silent)
	{
		$this->silent = $silent;
	}

	/**
	 *	Tells whether the output is silent or not
	 *
	 *	@return	bool 	true if silent, false otherwise
	 */
	public function silent()
	{
		return $this->silent;
	}

	/**
	 *	Adds a style from a tag specifier.
	 *
	 *	The tag must be in format <fg=color;bg=color;options=...>,
	 *	where color is a color name or a color code in #RGB or #RRGGBB format
	 *	and options is a comma-separated list of one or more effects
	 *
	 *	@param	string	$name	name of the style
	 *	@param	string	$spec	the style tag in the format above 
	 *	@return	void
	 */
	public function addStyle(string $name, string $spec)
	{
		$this->styles[$name] = OutputFormat::fromTag($name, $spec);
	}

	/**
	 *	Outputs a text to the console. It accepts styler tags too.
	 *
	 *	@param	string	$content	text to be written
	 *	@return	void
	 */
	public function write(string $content)
	{
		if ($this->silent())
		{
			return;
		}

		$params = [];

		if (OutputStyleParser::parse($content, $params))
		{
			if ($params['type'] == OutputStyleParser::TAGTYPE_FULL)
			{
				OutputFormat::fromTag('g', $content)->write($params['text']);
			}
			elseif ($params['type'] == OutputStyleParser::TAGTYPE_CUSTOM)
			{
				$this->getStyle($params['name'])->write($params['text']);
			}
			else
			{
				$this->defaultStyle->write($content);
			}
		}
		else
		{
			$this->defaultStyle->write($content);
		}
	}

	/**
	 *	Outputs a text to the console, plus a newline. It accepts styler tags too.
	 *
	 *	@param	string	$content	text to be written
	 *	@return	void
	 */
	public function writeln(string $content)
	{
		if ($this->silent())
		{
			return;
		}

		$this->write($content);
		$this->newLine();
	}

	/**
	 *	Outputs one or more empty lines to the console
	 *
	 *	@param	int		$count	number of lines to be written
	 *	@return	void
	 *	@throws	\InvalidArgumentException
	 */
	public function newLine(int $count = 1)
	{
		if ($this->silent())
		{
			return;
		}

		if ($count < 1)
		{
			throw new InvalidArgumentException('$count must be greater than zero!');
		}

		Co::newLine($count);
	}

	/**
	 *	Build a OutputStyler instance with a bunch of styles
	 *
	 *	@param	array	$specs	associated array of tags indexed by their style names
	 *	@return	\Collei\Console\Output\OutputStyler	
	 */
	public static function build(array $specs)
	{
		$output = new self();

		foreach ($specs as $name => $spec)
		{
			$output->addStyle($name, $spec);
		}

		return $output;
	}

}
