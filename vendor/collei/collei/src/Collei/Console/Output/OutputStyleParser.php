<?php
namespace Collei\Console\Output;

/**
 *	Encapsulates the command parser
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-11-xx
 */
class OutputStyleParser
{
	/**
	 *	@const string TAG_START
	 *	@const string TAG_CUSTOM
	 *	@const string TAG_FULL
	 */
	private const TAG_START = '/<(\s*fg\s*=\s*([^;>]+))?\s*;?(\s*bg\s*=\s*([^;>]+))?;?(\s*options\s*=\s*(\w+(\s*,\s*\w+)*)\s*)?\s*>/i';
	private const TAG_CUSTOM = '/<\s*(\w+)\s*>([^<]*)<\s*\/\1?\s*>/i';
	private const TAG_FULL = '/<(\s*fg\s*=\s*([^;>]+))?\s*;?(\s*bg\s*=\s*([^;>]+))?;?(\s*options\s*=\s*(\w+(\s*,\s*\w+)*)\s*)?\s*>([^<]*)<\s*\/\s*>/i';

	/**
	 *	@const string TAGTYPE_START
	 *	@const string TAGTYPE_CUSTOM
	 *	@const string TAGTYPE_FULL
	 */
	public const TAGTYPE_START = 'start';
	public const TAGTYPE_CUSTOM = 'custom';
	public const TAGTYPE_FULL = 'full';

	/**
	 *	parses a start-type styler tag
	 *
	 *	@param	string	$tag
	 *	@return	array|bool
	 */
	private static function parseTypeStart(string $tag)
	{
		$matches = [];
		//
		if (preg_match(self::TAG_START, $tag, $matches))
		{
			$list = str_replace(' ', '', $matches[6] ?? '');
			//
			if (!empty($list))
			{
				$list = explode(',', $list);
			}
			else
			{
				$list = [];
			}
			//
			return [
				'type' => self::TAGTYPE_START,
				'fg' => $matches[2] ?? 'default',
				'bg' => $matches[4] ?? 'default',
				'options' => $list,
			];
		}
		//
		return false;
	}

	/**
	 *	parses a custom tag
	 *
	 *	@param	string	$tag
	 *	@return	array|bool
	 */
	private static function parseTypeCustom(string $tag)
	{
		$matches = [];
		//
		if (preg_match(self::TAG_CUSTOM, $tag, $matches))
		{
			return [
				'type' => self::TAGTYPE_CUSTOM,
				'name' => $matches[1] ?? 'default',
				'text' => $matches[2] ?? '',
			];
		}
		//
		return false;
	}

	/**
	 *	parses a custom tag
	 *
	 *	@param	string	$tag
	 *	@return	array|bool
	 */
	private static function parseTypeFull(string $tag)
	{
		$matches = [];
		//
		if (preg_match(self::TAG_FULL, $tag, $matches))
		{
			$list = str_replace(' ', '', $matches[6] ?? '');
			//
			if (!empty($list))
			{
				$list = explode(',', $list);
			}
			else
			{
				$list = [];
			}
			//
			return [
				'type' => self::TAGTYPE_FULL,
				'fg' => $matches[2] ?? 'default',
				'bg' => $matches[4] ?? 'default',
				'options' => $list,
				'text' => $matches[8] ?? '',
			];
		}
		//
		return false;
	}

	/**
	 *	parses a styler tag
	 *
	 *	@param	string	a styler tag
	 *	@return	array
	 */
	public static function parse(string $tagFormat, array &$params)
	{
		$old = $params;
		//
		if ($params = self::parseTypeCustom($tagFormat))
		{
			return true;
		}
		//
		if ($params = self::parseTypeFull($tagFormat))
		{
			return true;
		}
		//
		if ($params = self::parseTypeStart($tagFormat))
		{
			return true;
		}
		//
		$params = $old;
		//
		return false;
	}

}
