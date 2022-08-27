<?php
namespace ColleiLang;

use ColleiLang\ColleiEnum;
use ColleiLang\Morphology\Term;
use ColleiLang\Morphology\Person;
use ColleiLang\Morphology\VowelHarmony;
use ColleiLang\Morphology\Conjugator;
use ColleiLang\Morphology\Number;
use ColleiLang\Morphology\NominalCase;
use ColleiLang\Morphology\Nouns\Noun;
use ColleiLang\Morphology\Verbs\Verb;
use ColleiLang\Morphology\Verbs\VerbTense;
use ColleiLang\Morphology\Verbs\VerbVoice;
use ColleiLang\Morphology\Verbs\VerbMode;
use ColleiLang\Morphology\Verbs\VerbPerson;
use ColleiLang\Morphology\Verbs\VerbDefiniteness;

/**
 *	Base engine for using Collei Language plugin capabilities
 *
 *	@author Collei Inc. <collei@collei.com.br>
 *	@author Alarido <alarido.su@gmail.com>
 *	@since 2022-08-08
 */
class Engine
{

	public static function definitenesses()
	{
		return VerbDefiniteness::asArray();
	}
	
	public static function voices()
	{
		return VerbVoice::asArray();
	}
	
	public static function modes()
	{
		return VerbMode::asArray();
	}
	
	public static function tenses()
	{
		return VerbTense::asArray();
	}
	
	public static function persons()
	{
		return VerbPerson::asArray();
	}

	public static function numbers()
	{
		return Number::asArray();
	}

	public static function cases()
	{
		return NominalCase::asArray();
	}

	public static function listOf(string $type)
	{
		$typex = $type;
		$type = \strtolower(\trim($type));
		//
		switch ($type) {
			case 'noun:person': // no used
			case 'noun:persons':
				return Person::asArray();
			case 'noun:number': // no used
			case 'noun:numbers':
				return Number::asArray();
			case 'noun:case': // no used
			case 'noun:cases':
				return NominalCase::asArray();
			case 'verb:person': // no used
			case 'verb:persons':
				return VerbPerson::asArray();
			case 'verb:mode': // no used
			case 'verb:modes':
				return VerbMode::asArray();
			case 'verb:tense': // no used
			case 'verb:tenses':
				return VerbTense::asArray();
			case 'verb:voice': // no used
			case 'verb:voices':
				return VerbVoice::asArray();
			case 'verb:definiteness': // no used
			case 'verb:definitenesses':
				return VerbDefiniteness::asArray();
			default:
				break;
		}
		//
		throw new InvalidArgumentException(
			"There is no such list: \"$typex\"."
		);
	}

	public static function createNoun(string $content)
	{
		return new Noun($content);
	}

	public static function createVerb(string $content)
	{
		return new Verb($content);
	}

	public static function inflectVerb(
		Verb $verb,
		VerbPerson $person = null,
		VerbTense $tense = null,
		VerbMode $mode = null,
		VerbVoice $voice = null,
		VerbDefiniteness $definiteness = null
	) {
		return Conjugator::inflect(
			$verb, $person, $tense, $mode, $voice, $definiteness
		);
	}

}

