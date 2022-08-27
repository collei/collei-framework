<?php
namespace ColleiLang\Morphology\Verbs;

use ColleiLang\Morphology\Term;
use ColleiLang\Morphology\Conjugator;
use InvalidArgumentException;

/**
 *	Embodies Verb features and actions 
 *
 *	@author Collei Inc. <collei@collei.com.br>
 *	@author Alarido <alarido.su@gmail.com>
 *	@since 2022-08-08
 */
class Verb extends Term
{
	/**
	 *	@var \ColleiLang\Morphology\Term $term
	 */
	private $stem;

	/**
	 *	Creates a new verb
	 *
	 *	@param	string	$verb
	 *	@return	self
	 */
	public function __construct(string $verb)
	{
		parent::__construct($verb);
		//
		if (\strtolower($this->last(2)) != 'da') {
			throw new InvalidArgumentException(
				"{$verb} is not a valid infinitive verb !"
			);
		}
		//
		$this->stem = new Term(
			\substr($verb, 0, -2)
		);
	}

	/**
	 *	Returns the stem of the verb
	 *
	 *	@return	string
	 */
	public function getStem()
	{
		return (string)$this->stem;
	}

	/**
	 *	Returns whether the verb stem ends with vowel
	 *
	 *	@return	bool
	 */
	public function endsInVowel()
	{
		return $this->stem->endsInVowel();
	}

	/**
	 *	Returns whether the verb stem has VowelHarmony equals Front
	 *
	 *	@return	bool
	 */
	public function isFront()
	{
		return $this->stem->isFront();
	}

	/**
	 *	Returns whether the verb stem has VowelHarmony equals Back
	 *
	 *	@return	bool
	 */
	public function isBack()
	{
		return $this->stem->isBack();
	}

	/**
	 *	Returns the correspondent verbal form according to parameters
	 *
	 *	@return	bool
	 */
	public function conjugate(
		VerbPerson $person = null,
		VerbTense $tense = null,
		VerbMode $mode = null,
		VerbVoice $voice = null,
		VerbDefiniteness $definiteness = null
	) {
		return Conjugator::inflect(
			$this, $person, $tense, $mode, $voice, $definiteness
		);
	}

}

