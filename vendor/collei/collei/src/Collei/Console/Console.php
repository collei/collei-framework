<?php
namespace Collei\Console;

use Collei\Console\Co;
use Collei\Console\Output\RGBColor;
use Collei\System\Terminal;
use Collei\Support\Number;
use Collei\Support\Values\Capsule;

/**
 *	This class encapsulates basic tasks with console 
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-10
 */
class Console extends Terminal
{
	/**
	 *	this resets on console all current color and effects once applied
	 */
	public const ANSI_RESET = "\x00\x1b[0m";

	public const ANSI_PREFIX_HEX = "\x00\x1b";
	public const ANSI_PREFIX_OCTAL = "\033";

	/**
	 *	the effects
	 */
	public const ANSI_EFFECT_BOLD = "\x00\x1b[1m";
	public const ANSI_EFFECT_UNDERLINE = "\x00\x1b[4m";
	public const ANSI_EFFECT_REVERSED = "\x00\x1b[7m";

	/**
	 *	the set of 8 foreground colors
	 */
	public const ANSI_COLOR_BLACK = "\x00\x1b[30m";
	public const ANSI_COLOR_RED = "\x00\x1b[31m";
	public const ANSI_COLOR_GREEN = "\x00\x1b[32m";
	public const ANSI_COLOR_YELLOW = "\x00\x1b[33m";
	public const ANSI_COLOR_BLUE = "\x00\x1b[34m";
	public const ANSI_COLOR_MAGENTA = "\x00\x1b[35m";
	public const ANSI_COLOR_CYAN = "\x00\x1b[36m";
	public const ANSI_COLOR_WHITE = "\x00\x1b[37m";
	public const ANSI_COLOR_DEFAULT = "\x00\x1b[39m";

	/**
	 *	the bright version of the previous set above
	 */
	public const ANSI_COLOR_BRIGHT_BLACK = "\x00\x1b[30;1m";
	public const ANSI_COLOR_BRIGHT_RED = "\x00\x1b[31;1m";
	public const ANSI_COLOR_BRIGHT_GREEN = "\x00\x1b[32;1m";
	public const ANSI_COLOR_BRIGHT_YELLOW = "\x00\x1b[33;1m";
	public const ANSI_COLOR_BRIGHT_BLUE = "\x00\x1b[34;1m";
	public const ANSI_COLOR_BRIGHT_MAGENTA = "\x00\x1b[35;1m";
	public const ANSI_COLOR_BRIGHT_CYAN = "\x00\x1b[36;1m";
	public const ANSI_COLOR_BRIGHT_WHITE = "\x00\x1b[37;1m";

	/**
	 *	the set of 8 background colors
	 */
	public const ANSI_BGCOLOR_BLACK = "\x00\x1b[40m";
	public const ANSI_BGCOLOR_RED = "\x00\x1b[41m";
	public const ANSI_BGCOLOR_GREEN = "\x00\x1b[42m";
	public const ANSI_BGCOLOR_YELLOW = "\x00\x1b[43m";
	public const ANSI_BGCOLOR_BLUE = "\x00\x1b[44m";
	public const ANSI_BGCOLOR_MAGENTA = "\x00\x1b[45m";
	public const ANSI_BGCOLOR_CYAN = "\x00\x1b[46m";
	public const ANSI_BGCOLOR_WHITE = "\x00\x1b[47m";
	public const ANSI_BGCOLOR_DEFAULT = "\x00\x1b[49m";

	/**
	 *	the bright version of the previous set above
	 */
	public const ANSI_BGCOLOR_BRIGHT_BLACK = "\x00\x1b[40;1m";
	public const ANSI_BGCOLOR_BRIGHT_RED = "\x00\x1b[41;1m";
	public const ANSI_BGCOLOR_BRIGHT_GREEN = "\x00\x1b[42;1m";
	public const ANSI_BGCOLOR_BRIGHT_YELLOW = "\x00\x1b[43;1m";
	public const ANSI_BGCOLOR_BRIGHT_BLUE = "\x00\x1b[44;1m";
	public const ANSI_BGCOLOR_BRIGHT_MAGENTA = "\x00\x1b[45;1m";
	public const ANSI_BGCOLOR_BRIGHT_CYAN = "\x00\x1b[46;1m";
	public const ANSI_BGCOLOR_BRIGHT_WHITE = "\x00\x1b[47;1m";

	/**
	 *	the console cursor controls
	 */
	public const ANSI_CURSOR_POSXY = "\x00\x1b[<L>;<C>H";
	public const ANSI_CURSOR_POSXY_ALT = "\x00\x1b[<L>;<C>f";
	public const ANSI_CURSOR_MOVE_UP = "\x00\x1b[<N>A";
	public const ANSI_CURSOR_MOVE_DOWN = "\x00\x1b[<N>B";
	public const ANSI_CURSOR_MOVE_RIGHT = "\x00\x1b[<N>C";
	public const ANSI_CURSOR_MOVE_LEFT = "\x00\x1b[<N>D";

	/**
	 *	the console general controls
	 */
	public const ANSI_CLEAR_SCREEN = "\x00\x1b[2J";
	public const ANSI_CLEAR_LINE = "\x00\x1b[2K";
	public const ANSI_CLEAR_EOL = "\x00\x1b[K";
	public const ANSI_CLEAR_CURSOR_EOL = "\x00\x1b[0K";
	public const ANSI_CLEAR_CURSOR_BOL = "\x00\x1b[1K";

	/**
	 *	the console dimensions
	 */
	private static $consoleDimensions = null;

	/**
	 *	the predefined choice messages
	 */
	private static $MESSAGE_CHOICE = [
		'askerMultiple'			=> 'Up to {many} options, separated by space',
		'askerUnique'			=> 'Just one option (enter 0 to leave)',
		'choiceErrorInvalid'	=> 'Wrong option! Must be an option number',
		'choiceLeave'			=> 'No option selected.',
		'choiceAttempt'			=> 'You have {chances} chances left. Try?',
	];

	/**
	 *	Returns the ANSI console escape codes corresponding to the color
	 *	@static
	 *	@param	int|string	$color	name or number of the color	
	 *	@param	bool		$bg		true for background, false for foreground	
	 *	@return	string	
	 */
	public static function getColor($color, bool $bg = false)
	{
		if (is_numeric($color)) {
			$value = Number::constrict((int)$color, 0, 255);
			$ground = $bg ? '48' : '38';
			//
			return "\x00\x1b[{$ground};5;{$value}m";
		} elseif (
			preg_match('/^\#[0-9A-Fa-f]{3}$/', $color) ||
			preg_match('/^\#[0-9A-Fa-f]{6}$/', $color)
		) {
			$value = RGBColor::hexToAnsiConsole216($color);
			$ground = $bg ? '48' : '38';
			//
			return "\x00\x1b[{$ground};5;{$value}m";
		} else {
			switch ($color) {
				case 'black':		return $bg ? self::ANSI_BGCOLOR_BLACK : self::ANSI_COLOR_BLACK;
				case 'red':			return $bg ? self::ANSI_BGCOLOR_RED : self::ANSI_COLOR_RED;
				case 'green':		return $bg ? self::ANSI_BGCOLOR_GREEN : self::ANSI_COLOR_GREEN;
				case 'yellow':		return $bg ? self::ANSI_BGCOLOR_YELLOW : self::ANSI_COLOR_YELLOW;
				case 'blue':		return $bg ? self::ANSI_BGCOLOR_BLUE : self::ANSI_COLOR_BLUE;
				case 'magenta':		return $bg ? self::ANSI_BGCOLOR_MAGENTA : self::ANSI_COLOR_MAGENTA;
				case 'cyan':		return $bg ? self::ANSI_BGCOLOR_CYAN : self::ANSI_COLOR_CYAN;
				case 'white':		return $bg ? self::ANSI_BGCOLOR_WHITE : self::ANSI_COLOR_WHITE;
				case 'default':		return $bg ? self::ANSI_BGCOLOR_DEFAULT : self::ANSI_COLOR_DEFAULT;
				case 'gray':			return $bg ? self::ANSI_BGCOLOR_BRIGHT_BLACK : self::ANSI_COLOR_BRIGHT_BLACK;
				case 'bright-red':		// no break
				case 'light-red':		return $bg ? self::ANSI_BGCOLOR_BRIGHT_RED : self::ANSI_COLOR_BRIGHT_RED;
				case 'bright-green':	// no break
				case 'light-green':		return $bg ? self::ANSI_BGCOLOR_BRIGHT_GREEN : self::ANSI_COLOR_BRIGHT_GREEN;
				case 'bright-yellow':	// no break
				case 'light-yellow':	return $bg ? self::ANSI_BGCOLOR_BRIGHT_YELLOW : self::ANSI_COLOR_BRIGHT_YELLOW;
				case 'bright-blue':		// no break
				case 'light-blue':		return $bg ? self::ANSI_BGCOLOR_BRIGHT_BLUE : self::ANSI_COLOR_BRIGHT_BLUE;
				case 'bright-magenta':	// no break
				case 'light-magenta':	return $bg ? self::ANSI_BGCOLOR_BRIGHT_MAGENTA : self::ANSI_COLOR_BRIGHT_MAGENTA;
				case 'bright-cyan':		// no break
				case 'light-cyan':		return $bg ? self::ANSI_BGCOLOR_BRIGHT_CYAN : self::ANSI_COLOR_BRIGHT_CYAN;
				case 'bright-white':	// no break
				case 'light-white':		return $bg ? self::ANSI_BGCOLOR_BRIGHT_WHITE : self::ANSI_COLOR_BRIGHT_WHITE;
			}
		}
		//
		return '';
	}

	/**
	 *	Returns the ANSI console escape codes corresponding to the effect
	 *	@static
	 *	@param	string	$effect	name of the effect	
	 *	@return	string	
	 */
	public static function getEffect(string $effect)
	{
		switch ($effect) {
			case 'bold':		return self::ANSI_EFFECT_BOLD;
			case 'blink':		return self::ANSI_EFFECT_BOLD;
			case 'underline':	// no break
			case 'underscore':	return self::ANSI_EFFECT_UNDERLINE;
			case 'reversed':	return self::ANSI_EFFECT_REVERSED;
		}
		//
		return '';
	}

	/**
	 *	@var array $effects
	 */
	private static $effects = [];

	/**
	 *	@var string $color
	 */
	private static $color = '';

	/**
	 *	validates ansi color and effect codes
	 *	@static
	 *	@return	bool
	 */
	private static function valid(string $codes)
	{
		return preg_match('/((\\x00\\x1b|\x00\x1b)\[(\d{1,3};?)*m)+/', $codes);
	}

	/**
	 *	reapply reset settings
	 *	@static
	 *	@return	void
	 */
	private static function reapplyEffects()
	{
		foreach (self::$effects as $effect) {
			echo $effect;
		}
	}

	/**
	 *	set current color
	 *	@static
	 *	@param string $color the color in ANSI console format
	 *	@return	void
	 */
	private static function setAnsiColor(string $color)
	{
		self::$color = $color;
		echo $color;
	}

	/**
	 *	adds a effect
	 *	@static
	 *	@param string $effect the effect in ANSI console format
	 *	@return	void
	 */
	private static function setAnsiEffect(string $effect)
	{
		self::$effects[] = $effect;
		echo $effect;
	}

	/**
	 *	removes the last applied effect
	 *	@static
	 *	@return	void
	 */
	private static function resetLastEffect()
	{
		array_pop(self::$effects);
		//
		self::reset();
		self::reapplyEffects();
		//
		echo self::$color;
	}

	/**
	 *	Set console bold effect
	 *	@static
	 *	@return	void
	 */
	public static function setBold()
	{
		self::setAnsiEffect(self::ANSI_EFFECT_BOLD);
	}

	/**
	 *	Set console underline effect
	 *	@static
	 *	@return	void
	 */
	public static function setUnderline()
	{
		self::setAnsiEffect(self::ANSI_EFFECT_UNDERLINE);
	}

	/**
	 *	Set console reversed effect
	 *	@static
	 *	@return	void
	 */
	public static function setReversed()
	{
		self::setAnsiEffect(self::ANSI_EFFECT_REVERSED);
	}

	/**
	 *	Reset last applied effect
	 *	@static
	 *	@return	void
	 */
	public static function resetLast()
	{
		self::resetLastEffect();
	}

	/**
	 *	Reset colors
	 *	@static
	 *	@return	void
	 */
	public static function resetColors()
	{
		self::reset();
		self::reapplyEffects();
	}

	/**
	 *	Set console foreground color (e.g., #F00, #FF0000, 196, red, bright-red)
	 *
	 *	there is a set of valid color names as below, both for fore- and background:
	 *		black, red, green, yellow, blue, magenta, cyan, white, gray,
	 *		bright-red, bright-green, bright-yellow, bright-blue,
	 *		bright-magenta, bright-cyan, bright-white
	 *	@static
	 *	@param	string		one of the valid names above
	 */
	public static function setColor(string $color)
	{
		self::setAnsiColor(self::getColor($color, false));
	}

	/**
	 *	Set console background color (e.g., #F00, #FF0000, 196, red, bright-red)
	 *
	 *	there is a set of valid color names as below, both for fore- and background:
	 *		black, red, green, yellow, blue, magenta, cyan, white, gray,
	 *		bright-red, bright-green, bright-yellow, bright-blue,
	 *		bright-magenta, bright-cyan, bright-white
	 *	@static
	 *	@param	string		one of the valid names above
	 */
	public static function setBackgroundColor(string $color)
	{
		self::setAnsiColor(self::getColor($color, true));
	}

	/**
	 *	Reset any console color and effects
	 *	@static
	 *	@return	void
	 */
	public static function reset()
	{
		echo self::ANSI_RESET;
	}

	/**
	 *	reset color and remember reapply effects
	 *	@static
	 *	@return	void
	 */
	public static function resetColor()
	{
		self::$color = '';
		self::reset();
		self::reapplyEffects();
	}

	/**
	 *	Returns the console dimensions 
	 *	@static
	 *	@return	Capsule
	 */
	public static function dimensions()
	{
		if (null === self::$consoleDimensions)
		{
			$term = new Terminal();
			//
			self::$consoleDimensions = Capsule::from([
				'width' => $term->getColumnCount(),
				'height' => $term->getRowCount(),
			]);
		}
		//
		return self::$consoleDimensions;
	}

	/** 
	 *	Sets the cursor position (X,Y). Constrained by the current
	 *	screen dimensions. Only positive numbers allowed. Negatives
	 *	get "rounded" to zero.
	 *	@static
	 *	@param	int	$x
	 *	@param	int	$y
	 *	@return	void
	 */
	public static function moveTo(int $x, int $y)
	{
		$screen = self::dimensions();
		//
		$x = '' . (($x > $screen->width)
			? $screen->width
			: (($x < 0) ? 0 : $x));
		//
		$y = '' . (($y > $screen->height)
			? $screen->height
			: (($y < 0) ? 0 : $y));
		//
		$realcode = str_replace(
			['<L>', '<C>'], [$y, $x], self::ANSI_CURSOR_POSXY
		);
		//
		echo $realcode;
	}

	/** 
	 *	Sets the cursor position (X,Y). Imposes no screen limits by itself.
	 *	Accepts both positive and negative numbers.
	 *	@static
	 *	@param	int	$x
	 *	@param	int	$y
	 *	@return	void
	 */
	public static function moveBy(int $x, int $y)
	{
		$x_code = ($x >= 0)
			? self::ANSI_CURSOR_MOVE_RIGHT
			: self::ANSI_CURSOR_MOVE_LEFT; 
		$y_code = ($y >= 0)
			? self::ANSI_CURSOR_MOVE_DOWN
			: self::ANSI_CURSOR_MOVE_UP;
		//
		$x = '' . \abs($x);
		$y = '' . \abs($y);
		//
		$realcode = ''
			. str_replace('<N>', $x, $x_code) 
			. str_replace('<N>', $y, $y_code);
		//
		echo $realcode;
	}

	/**
	 *	Clears the whole screen
	 *	@static
	 *	@return	void
	 */
	public static function clearScreen()
	{
		echo self::ANSI_CLEAR_SCREEN;
	}

	/**
	 *	Clears from the current cursor position to the end of line.
	 *	If $line is not given, clears at current cursor position.
	 *	@static
	 *	@param	int		$line = null
	 *	@return	void
	 */
	public static function clearLine(int $line = null)
	{
		if (null !== $line) {
			$line = \abs($line);
			$screen = self::dimensions();
			//
			$line = '' . (($line > $screen->height)
				? $screen->height
				: $line);
			//
			$realcode = str_replace(
				['<L>','<C>'], [$line, '1'], self::ANSI_CURSOR_POSXY
			);
			//
			echo $realcode;
		}
		//
		echo self::ANSI_CLEAR_LINE;
	}

	/**
	 *	Clears from the current cursor position to the end of line
	 *	@static
	 *	@return	void
	 */
	public static function clearEndLine()
	{
		echo self::ANSI_CLEAR_EOL;
	}

	/**
	 *	Writes the specified number of newlines
	 *	@static
	 *	@param	int	$count = 1
	 */
	public static function newLine(int $count = 1)
	{
		for ($i = 0; $i < $count; $i++) {
			echo "\r\n";
		}
	} 

	/**
	 *	Writes text in console with the specified color and then reset color
	 *	@static
	 *	@param	mixed	$text		value to be written
	 *	@param	mixed	$fgColor	foreground color	
	 *	@param	mixed	$bgColor	background color	
	 *	@param	array	$options	one or more effects (bold, reversed, underline, underscore)	
	 */
	public static function write(
		$text,
		$fgColor = 'default',
		$bgColor = 'default',
		array $options = []
	) {
		$ansiColors = self::getColor($fgColor, false) . self::getColor($bgColor, true);
		//
		foreach ($options as $option) {
			$ansiColors .= self::getEffect($option);
		}
		//
		echo $ansiColors . $text . self::ANSI_RESET;
	}

	/**
	 *	Writes text in console with the specified color and then reset color
	 *	starting from a specific screen position (x,y)
	 *	@static
	 *	@param	int		$x			console column position	
	 *	@param	int		$y			console row position
	 *	@param	mixed	$text		value to be written
	 *	@param	mixed	$fgColor	foreground color	
	 *	@param	mixed	$bgColor	background color	
	 *	@param	array	$options	one or more effects (bold, reversed, underline, underscore)	
	 */
	public static function writeTo(
		int $x,
		int $y,
		$text,
		$fgColor = 'default',
		$bgColor = 'default',
		array $options = []
	) {
		self::moveTo($x, $y);
		self::write($text, $fgColor, $bgColor, $options);
	}

	/**
	 *	Writes text in console with the specified color and then reset color
	 *	starting from a specific screen position (x,y)
	 *	@static
	 *	@param	int		$left		console first column position	
	 *	@param	int		$top		console first row position
	 *	@param	int		$right		console last column position
	 *	@param	int		$bottom		console last row position
	 *	@param	mixed	$text		value to be written
	 *	@param	mixed	$fgColor	foreground color	
	 *	@param	mixed	$bgColor	background color	
	 *	@param	array	$options	one or more effects (bold, reversed, underline, underscore)	
	 */
	public static function writeInRect(
		int $left,
		int $top,
		int $right,
		int $bottom,
		$text,
		$fgColor = 'default',
		$bgColor = 'default',
		array $options = []
	) {
		$ansiColors = self::getColor($fgColor, false) . self::getColor($bgColor, true);
		//
		foreach ($options as $option) {
			$ansiColors .= self::getEffect($option);
		}
		//
		$chi = 0;
		$line = 0;
		$textlen = \strlen($text);
		$characters = \str_split($text);
		//
		self::moveTo($left, $top);
		echo $ansiColors;
		//
		for ($j = $top; $j <= $bottom; ++$j) {
			for ($i = $left; $i <= $right; ++$i) {
				if ($chi >= $textlen) {
					break;
				}
				echo $characters[$chi];
				++$chi;
			}
			++$line;
			self::moveTo($left, $top + $line);
		}
		//
		echo self::ANSI_RESET;
	}

	/**
	 *	Reads a line of user input from the console
	 *	@static
	 *	@return	string	the user input
	 */
	public static function read()
	{
		return fgets(STDIN);
	}

	/**
	 *	Reads a single char from the console
	 *	@static
	 *	@param	string	$prompt = ""
	 *	@return	string
	 */
	public static function readchar(string $prompt = "")
	{
		if (!empty($prompt)) {
			echo($prompt . ':');
		}
		//
		while (true) {
			//clear buffer - read all unwanted characters
			while(fgetc(STDIN) != "\n");
			//get first character from STDIN
			return fgetc(STDIN);
		}
	}

	/**
	 *	Reads a line of user input from the console after prompting
	 *	@static
	 *	@return	string	the user input
	 */
	public static function prompt(
		string $prompt,
		$color = null,
		bool $arrow = false
	) {
		if (!is_null($color) && self::valid($color)) {
			self::setAnsiColor($color);
			echo(trim($prompt) . ($arrow ? ': ' : ''));
			self::resetColor();
			echo(' ');
		} else {
			echo(trim($prompt) . ($arrow ? ': ' : ''));
		}
		//
		$input = fgets(STDIN);
		usleep(6250);
		return $input;
	}

	/**
	 *	Reads a line of user input from the console - hiding it
	 *	@static
	 *	@return	string	the user input
	 */
	public static function silent(string $prompt, $color = null)
	{
		$secret = [];
		//
		if (!is_null($color) && self::valid($color)) {
			self::setAnsiColor($color);
			echo(trim($prompt) . ':');
			self::resetColor();
			echo(' ');
		} else {
			echo(trim($prompt) . ': ');
		}
		//
		exec('hiddeninput', $secret);
		//
		echo "\r\n";
		//
		return $secret[0];
	}

	/**
	 *	Reads a key from the keyboard
	 *	@static
	 *	@return	string	the user input
	 */
	public static function readKey()
	{
		$input = fgetc(STDIN);
		usleep(6250);
		return $input;
	}

	/**
	 *	Ask for confirmation
	 *	@static
	 *	@param	string	$prompt		Text to display
	 *	@param	bool	$default	Default response. Defaults to false
	 *	@return	bool
	 */
	public static function confirm(string $prompt, bool $default = false)
	{
		$resp = strtolower(trim(self::prompt($prompt)));
		//
		if (in_array($resp, ['y','yes','1'])) {
			return true;
		} elseif (in_array($resp, ['n','no','0'])) {
			return false;
		}
		//
		return $default;
	}


	/**
	 *	Reads choice from user, displaying available options
	 *	@static
	 *	@param	string	$prompt			the message to be displayed
	 *	@param	array	$options		list of options to be displayed
	 *	@param	int		$defaultIndex	default if no choice is made.
	 *	@param	int		$maxAttempts	how many attempts for invalid options.
	 *	@param	int		$maxSelections	how many options can bew selected.
	 *	@return	int
	 */
	public static function choice(
		string $prompt,
		array $options,
		int $defaultIndex = null,
		int $maxAttempts = 1,
		int $maxSelections = 1
	) {
		$attempt = 1;
		$optionCount = count($options);
		$maxAttempts = ($maxAttempts < 1) ? 1 : $maxAttempts;
		$maxSelections = ($maxSelections < 1) ? 1 : $maxSelections;
		//
		if (!is_null($defaultIndex)) {
			$defaultIndex = ($defaultIndex < 0)
				? 0
				: $defaultIndex;
			$defaultIndex = ($defaultIndex >= $optionCount)
				? ($optionCount - 1) 
				: $defaultIndex;
		}
		//
		echo "\r\n- $prompt:";
		//
		foreach ($options as $i => $option) {
			$n = $i + 1;
			echo "\r\n\t[ $n ] : $option";
		}
		//
		$asker = self::$MESSAGE_CHOICE['askerUnique'];
		//
		if ($maxSelections > 1) {
			$asker = str_replace(
				'{many}',
				$maxSelections,
				self::$MESSAGE_CHOICE['askerMultiple']
			);
		}
		//
		while ($attempt <= $maxAttempts) {
			$num = 0;
			//
			echo "\r\n -" . $asker . ' --> '; 
			//
			$num = trim(Co::read());
			//
			if (!is_numeric($num)) {
				echo "\r\n- " . self::$MESSAGE_CHOICE['choiceErrorInvalid'];
			} elseif ($num == 0) {
				echo "\r\n- " . self::$MESSAGE_CHOICE['choiceLeave'];
				return $defaultIndex;
			} elseif ($num < 1 || $num > $optionCount) {
				echo "\r\n- " . self::$MESSAGE_CHOICE['choiceErrorInvalid'];
			} else {
				return $num - 1;
			}
			//
			if ($attempt < $maxAttempts)
			{
				$leftCount = (string)($maxAttempts - $attempt);
				$message = "\r\n\r\n- " 
					. str_replace(
						'{chances}',
						$leftCount,
						self::$MESSAGE_CHOICE['choiceAttempt']
					)
					. " (Y/N): ";
				//
				if (!self::confirm($message)) {
					break;
				}
			}
			//
			++$attempt;
		}
		//
		return $defaultIndex;
	}

}

