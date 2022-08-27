<?php
namespace ColleiLang\Morphology;

use ColleiLang\Morphology\VowelHarmony;
use ColleiLang\Contracts\Vowels;

/**
 *	Shared features of every word 
 *
 *	@author Collei Inc. <collei@collei.com.br>
 *	@author Alarido <alarido.su@gmail.com>
 *	@since 2022-08-08
 */
class Term implements Vowels
{
	/**
	 *	@const array VOWELS_BACK
	 *	@const array VOWELS_FRONT
	 */
	private const VOWELS_BACK = ['a','A','o','O','u','U'];
	private const VOWELS_FRONT = ['e','E','i','I'];

	/**
	 *	@var string $term
	 */
	private $term = '';

	/**
	 *	Creates a new term
	 *
	 *	@param	string	$term
	 *	@return	self
	 */
	public function __construct(string $term)
	{
		$this->term = $term;
	}

	/**
	 *	Converts to string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return $this->term;
	}

	/**
	 *	@property string $last
	 *	@property string $lastVowel
	 *	@property bool $front
	 *	@property bool $back
	 */
	public function __get(string $name)
	{
		if ($name == 'last') {
			return $this->last();
		}
		if ($name == 'lastVowel') {
			return $this->lastVowel();
		}
		if ($name == 'front') {
			return $this->isFront();
		}
		if ($name == 'back') {
			return $this->isBack();
		}
	}

	/**
	 *	Returns the vowel harmony of term
	 *
	 *	@return	\ColleiLang\Morphology\VowelHarmony
	 */
	public function getHarmony()
	{
		if ($this->isFront()) {
			return VowelHarmony::new('Front');
		}
		if ($this->isBack()) {
			return VowelHarmony::new('Back');
		}
		return null;
	}

	/**
	 *	Returns the term as string
	 *
	 *	@return	string
	 */
	public function asString()
	{
		return $this->term;
	}

	/**
	 *	Returns the $count last characters of the term
	 *
	 *	@param	int	$count = 1
	 *	@return	string
	 */
	public function last(int $count = 1)
	{
		if ($count < 1) {
			$count = 1;
		}
		//
		return \substr($this->term, -$count);
	}

	/**
	 *	Returns the last vowel of the term
	 *
	 *	@return	string
	 */
	public function lastVowel()
	{
		$letters = \str_split(\strrev($this->term));
		//
		foreach ($letters as $ch) {
			if (\in_array($ch, self::VOWELS)) {
				return $ch;
			}
		}
		//
		return null;
	}

	/**
	 *	Returns whether the term ends with vowel
	 *
	 *	@return	bool
	 */
	public function endsInVowel()
	{
		return \in_array($this->last(), self::VOWELS);
	}

	/**
	 *	Returns whether the term has VowelHarmony equals Front
	 *
	 *	@return	bool
	 */
	public function isFront()
	{
		if ($ch = $this->lastVowel()) {
			return \in_array($ch, self::VOWELS_FRONT, true);
		}
		//
		return false;
	}

	/**
	 *	Returns whether the term has VowelHarmony equals Back
	 *
	 *	@return	bool
	 */
	public function isBack()
	{
		if ($ch = $this->lastVowel()) {
			return \in_array($ch, self::VOWELS_BACK, true);
		}
		//
		return false;
	}

}

