<?php
namespace ColleiLang\Morphology;

use ColleiLang\Morphology\Person;
use ColleiLang\Morphology\NominalCase;
use ColleiLang\Morphology\Number;
use ColleiLang\Morphology\Nouns\Noun;
use ColleiLang\Contracts\Vowels;
use ColleiLang\Contracts\Persons;
use ColleiLang\Contracts\Cases;
use InvalidArgumentException;

/**
 *	Conjugation engine
 *
 *	@author Collei Inc. <collei@collei.com.br>
 *	@author Alarido <alarido.su@gmail.com>
 *	@since 2022-08-10
 */
class Declensor implements Vowels, Persons, Cases
{
	/**
	 *	@const array PERSON_DESINENCES
	 */
	private const PERSON_DESINENCES = [
		'Front' => [
			'Singular' => [
				0 => [null, 't', 'n', 'nek', 'ker', 'ni', 've', 'no', 'le', 'to'],
				1 => [null, 'et', 'en', 'nek', 'ker', 'ni', 've', 'no', 'le', 'to']
			],
			'Plural' => [
				0 => [null, 'rat', 'nim', 'nekim', 'kerim', 'rani', 'rave', 'rano', 'rale', 'rato'],
				1 => [null, 'imet', 'enim', 'nekim', 'kerim', 'imni', 'imve', 'imno', 'imle', 'imto']
			]
		],
		'Back' => [
			'Singular' => [
				0 => [null, 't', 'n', 'nak', 'kar', 'ni', 've', 'no', 'le', 'to'],
				1 => [null, 'ot', 'en', 'nak', 'kar', 'ni', 've', 'no', 'le', 'to']
			],
			'Plural' => [
				0 => [null, 'rat', 'nim', 'nakim', 'karim', 'rani', 'rave', 'rano', 'rale', 'rato'],
				1 => [null, 'imot', 'enim', 'nakim', 'karim', 'imni', 'imve', 'imno', 'imle', 'imto']
			]
		]
	];

	/**
	 *	@const array DECLENSION_EXCLUSIONS
	 */
	private const DECLENSION_EXCLUSIONS = [
		'mi' => ['mi', 'mett', 'men', 'nekem', 'kerem', 'mini', 'mive', 'mino', 'mile', 'mito'],
		'ti' => ['ti', 'tett', 'ten', 'neked', 'kered', 'tini', 'tive', 'tino', 'tile', 'tito'],
		'on' => ['on', 'ot',   'on',  'neki',  'keri',  'onni', 'onve', 'onno', 'onle', 'onto'],
		'biz' => ['biz', 'bizt', 'ben', 'nekenu',  'kerenu',  'bizni', 'bizve', 'bizno', 'bizle', 'bizto'],
		'tiz' => ['tiz', 'tizt', 'ten', 'nekitek', 'keritek', 'tizni', 'tizve', 'tizno', 'tizle', 'tizto'],
		'onk' => ['onk', 'kont', 'kon', 'nekyuk',  'keryuk',  'onkni', 'onkve', 'onkno', 'onkle', 'onkto'],
	];

	/**
	 *	@const array PLURAL_PATTERNS
	 */
	private const PLURAL_PATTERNS = [
		'um' => 'a',
		'ra' => 'ri',
		'a' => 'ara',
		'e' => 'era',
		'i' => 'im',
		'o' => 'ora',
		'u' => 'ura',
		'' => 'im'
	];

	/**
	 *	@const array NUMBER_DESINENCES
	 */
	private const NUMBER_DESINENCES = [
		'Front' => [
			'Singular' => null,
			'Dual' => 'ler',
			'Plural' => self::PLURAL_PATTERNS
		],
		'Back' => [
			'Singular' => null,
			'Dual' => 'lar',
			'Plural' => self::PLURAL_PATTERNS
		]
	];

	/**
	 *	@const array POSSESSION_DESINENCES
	 */
	private const POSSESSION_DESINENCES = [
		'Front' => [
			'Singular' => [
				0 => ['m','d','y','nu','tek','yuk'],
				1 => ['em','ed','ye','enu','itek','yuk']
			],
			'Dual' => [
				0 => ['lerem','lered','lerye','lerenu','lertek','leryuk'],
				1 => ['lerem','lered','lerye','lerenu','lertek','leryuk']
			],
			'Plural' => [
				0 => ['im','id','iy','inu','itek','iyuk'],
				1 => ['eim','eid','eiye','einu','eitek','eiyuk']
			]
		],
		'Back' => [
			'Singular' => [
				0 => ['m','d','y','nu','tok','yuk'],
				1 => ['om','od','ya','onu','otok','yuk']
			],
			'Dual' => [
				0 => ['laram','larad','larya','laranu','lartok','laryuk'],
				1 => ['laram','larad','larya','laranu','lartok','laryuk']
			],
			'Plural' => [
				0 => ['im','id','iy','inu','itok','iyuk'],
				1 => ['oim','oid','oiya','oinu','oitok','oiyuk']
			]
		]
	];

	/**
	 *	@const array POSSESSION_EXCLUSIONS
	 */
	private const POSSESSION_EXCLUSIONS = [
		'nak' => ['nakom', 'nakod', 'nakya', 'nakanu', 'nakotok', 'nakyuk'],
		'nek' => ['nekem', 'neked', 'nekye', 'nekenu', 'nekitek', 'nekyuk'],
		'kar' => ['karom', 'karod', 'karya', 'karanu', 'karotok', 'karyuk'],
		'ker' => ['kerem', 'kered', 'kerye', 'kerenu', 'keritek', 'keryuk'],
		'ni' => ['nim', 'nid', 'niy', 'ninu', 'nitek', 'niyuk'],
	];

	/**
	 *	Generates declined forms
	 *	@static
	 *	@param	\ColleiLang\Morphology\NominalTerm	$nominal
	 *	@param	\ColleiLang\Morphology\Number	$number
	 *	@param	\ColleiLang\Morphology\NominalCase	$case
	 *	@return	string|null
	 */
	private static function generateForm(
		NominalTerm $nominal,
		Number $number,
		NominalCase $case
	) {
		$base = (string)$nominal;
		$baseLower = \strtolower($base);
		$caseId = \array_search((string)$case, self::CASES) ?: 0;
		//
		if (array_key_exists($baseLower, self::DECLENSION_EXCLUSIONS))
		{
			return self::DECLENSION_EXCLUSIONS[$baseLower][$caseId];
		}
		//
		$harmony = $nominal->getHarmony();
		$voweled = (\in_array($nominal->last(), self::VOWELS, true) ? 0 : 1);
		//
		if ($case->is('Nominative') && $number->is('Plural')) {
			foreach (self::PLURAL_PATTERNS as $from => $to) {
				if (empty($from)) {
					return $nominal->asString() . $to;
				} else {
					$suffixLength = \strlen($from);
					if ($nominal->last($suffixLength) == $from) {
						return \substr(
							$nominal->asString(), 0, -$suffixLength
						) . $to;
					}
				}
			}
		}
		//
		if ($number->is('Dual')) {
			$base .= self::NUMBER_DESINENCES[(string)$harmony]['Dual'];
			$voweled = 1;
			$numberStr = 'Singular';
		} else {
			$numberStr = (string)$number;
		}
		//
		$caseId = \array_search((string)$case, self::CASES);
		//
		if ($n = self::PERSON_DESINENCES[(string)$harmony] ?? false) {
			return $base . $n[$numberStr][$voweled][$caseId] ?? null;
		}
		//
		return null;
	}

	/**
	 *	Generates declined forms
	 *	@static
	 *	@param	\ColleiLang\Morphology\NominalTerm	$nominal
	 *	@param	\ColleiLang\Morphology\Person	$person
	 *	@param	\ColleiLang\Morphology\Number	$number
	 *	@return	string|null
	 */
	private static function generatePossessiveForm(
		NominalTerm $nominal,
		Person $person,
		Number $number
	) {
		$base = (string)$nominal;
		$baseLower = \strtolower($base);
		$personId = \array_search((string)$person, self::PERSONS) ?: 0;
		//
		if (array_key_exists($baseLower, self::POSSESSION_EXCLUSIONS))
		{
			return self::POSSESSION_EXCLUSIONS[$baseLower][$personId];
		}
		//
		$harmony = $nominal->getHarmony();
		$voweled = (\in_array($nominal->last(), self::VOWELS, true) ? 0 : 1);
		//
		if ($n = self::POSSESSION_DESINENCES[(string)$harmony] ?? false) {
			if (isset($n[(string)$number][$voweled][$personId])) {
				return $base . $n[(string)$number][$voweled][$personId];
			}
		}
		//
		return null;
	}
	
	//////////////////////
	////    public    ////
	//////////////////////

	/**
	 *	Generates declined forms
	 *	@static
	 *	@param	\ColleiLang\Morphology\NominalTerm	$nominalTerm
	 *	@param	\ColleiLang\Morphology\Number	$number
	 *	@param	\ColleiLang\Morphology\NominalCase	$case
	 *	@return	string|null
	 */
	public static function decline(
		NominalTerm $nominalTerm,
		Number $number,
		NominalCase $case
	) {
		return self::generateForm(
			$nominalTerm, $number, $case
		);
	}

	/**
	 *	Generates possessive forms
	 *	@static
	 *	@param	\ColleiLang\Morphology\NominalTerm	$nominalTerm
	 *	@param	\ColleiLang\Morphology\Person	$person
	 *	@param	\ColleiLang\Morphology\Number	$number
	 *	@return	string|null
	 */
	public static function declinePossessive(
		NominalTerm $nominalTerm,
		Person $person,
		Number $number
	) {
		$comp = \ucfirst(\strtolower($nominalTerm->asString()));
		if (\in_array($comp, self::PERSONS)) {
			throw new InvalidArgumentException(
				$nominalTerm . ' is not declinable on possessives.'
			);
		}
		//
		return self::generatePossessiveForm(
			$nominalTerm, $person, $number
		);
	}

}


