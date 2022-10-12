<?php
namespace Collei\Views;

use Closure;

/**
 *	Keeps the basic replacement engine
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class ConstructBase
{
	/**
	 *	constants
	 */
	public const CONSTRUCT_PREFIX = '@';
	public const CONSTRUCT_PREFIX_REGEX = '\\' . self::CONSTRUCT_PREFIX;

	/**
	 *	constants
	 */
	public const CONSTRUCT_SNIPPET_EMPTY = '';
	public const CONSTRUCT_SNIPPET_ELSE = '<?php } else { ?>';
	public const CONSTRUCT_SNIPPET_END = '<?php } ?>';

	/**
	 *	@var @static bool $initialized
	 */
	private static $initialized = false;

	/**
	 *	replacement constants
	 */
	private static $CONSTRUCT_SNIPPETS = [

		[
			'name'			=> 'comments',
			'regex'			=> '{{--\s?.*\s?--}}',
			'placement'		=> '',
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'auth',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'auth(\s*\(\'([\w\-]+)\'\))?',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'auth(.*)',
			'placement'		=> '<?php if (auth(\'$2\')) { ?>',
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'elseauth',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'elseauth(\s*\(\'([\w\-]+)\'\))?',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'elseauth(.*)',
			'placement'		=> '<?php } elseif (auth(\'$2\')) { ?>',
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'endauth',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endauth',
			'placement'		=> self::CONSTRUCT_SNIPPET_END,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'guest',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'guest',
			'placement'		=> '<?php if (guest()) { ?>',
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'endguest',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endguest',
			'placement'		=> self::CONSTRUCT_SNIPPET_END,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'else',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'else',
			'placement'		=> self::CONSTRUCT_SNIPPET_ELSE,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'isset',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'isset\s*\((.*)\)',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'isset(.*)',
			'placement'		=> '<?php if (isset($1)) { ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'endisset',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endisset',
			'placement'		=> self::CONSTRUCT_SNIPPET_END,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'empty',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'empty\s*\((.*)\)',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'empty(.*)',
			'placement'		=> '<?php if (empty($1)) { ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'endempty',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endempty',
			'placement'		=> self::CONSTRUCT_SNIPPET_END,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'if',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'if\s*\((.*)\)',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'if(.*)',
			'placement'		=> '<?php if ($1) { ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'elseif',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'elseif\s*\((.*)\)',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'elseif(.*)',
			'placement'		=> '<?php } elseif ($1) { ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'endif',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endif',
			'placement'		=> self::CONSTRUCT_SNIPPET_END,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'foreach',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'foreach(\s*\(\s*.*\s*as\s*(\$\w+\s*=>\s*)?\$\w+\s*\))',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'foreach(.*)',
			'placement'		=> '<?php foreach $1 { ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'endforeach',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endforeach',
			'placement'		=> self::CONSTRUCT_SNIPPET_END,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'forelse',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'forelse(\s*\(\s*(.+)\s*as\s*(\$\w+\s*=>\s*)?\$\w+\s*\))',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'forelse(.*)',
			'placement'		=> '<?php if (!empty($2)) { foreach $1 { ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'empty',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'empty',
			'placement'		=> '<?php } } else { ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'endforelse',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endforelse',
			'placement'		=> self::CONSTRUCT_SNIPPET_END,
			'auto-replace'	=> true
		],

		[
			'name'			=> 'inject',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'inject(\(\s*\'\s*(\w+)\s*\'\s*(,\s*(\'[^\']*\')\s*(,\s*.*\s*)*)?\s*\))',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'inject(.*)',
			'placement'		=> '<?php \$$2 = app($4$5); ?>',
			'auto-replace'	=> true,
			'validator'		=> [
				'closure' => 'has_class',
				'arg-indexes' => [ 4 ],
				'reason' => 'Class not found',
			]
		],

		[
			'name'			=> 'csrf_token',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'csrf_token',
			'placement'		=> '<?php echo(csrf()); ?>',
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'csrf',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'csrf',
			'placement'		=> '<input name="_token" type="hidden" value="<?php echo(csrf()); ?>">',
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'method',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'method\s*\(\'([^\']*)\'\)',
			'incomplete'	=> self::CONSTRUCT_PREFIX_REGEX . 'method(.*)',
			'placement'		=> '<input name="_method" type="hidden" value="$1">',
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'php',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'php',
			'placement'		=> "\r\n<?php\r\n",
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'endphp',
			'regex'			=> self::CONSTRUCT_PREFIX_REGEX . 'endphp',
			'placement'		=> "\r\n?>\r\n",
			'auto-replace'	=> true,
		],

		[
			'name'			=> 'rawecho',
			'regex'			=> '\{!!([^!]*)!!\}',
			'incomplete'	=> '\{!!([^!]*)!!\}',
			'placement'		=> '<?php echo(($1)); ?>',
			'auto-replace'	=> true
		],

		[
			'name'			=> 'echo',
			'regex'			=> '\{\{([^}]*)\}\}',
			'incomplete'	=> '\{\{([^}]*)\}\}',
			'placement'		=> '<?php echo(html_to_display($1)); ?>',
			'auto-replace'	=> true
		],
	];

	/**
	 *	Returns all replacement snippets properly organized
	 *
	 *	@return	array
	 */
	public static function getSnippetSets()
	{
		return self::$CONSTRUCT_SNIPPETS;
	}

	/**
	 *	Returns all replacement snippets properly organized
	 *
	 *	@return	array
	 */
	public static function getSnippets()
	{
		$snip_patterns = [];
		$snip_replacements = [];

		foreach (self::$CONSTRUCT_SNIPPETS as $snip)
		{
			if ($snip['auto-replace'])
			{
				if (substr($snip['regex'],0,1) != '#')
				{
					$snip_patterns[] = '#' . trim($snip['regex']) . '#';
				}
				else
				{
					//$snip_patterns[] = '#' . $snip['regex'] . '#';
					$snip_patterns[] = trim($snip['regex']);
				}
				//
				$snip_replacements[] = $snip['placement'];
			}
		}

		return [
			'pattern' => $snip_patterns,
			'replacement' => $snip_replacements,
		];
	}

}


