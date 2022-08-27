<?php
namespace ColleiLang\Morphology;

use ColleiLang\ColleiEnum;

/**
 *	Embodies Verb mode constants 
 *
 *	@author Collei Inc. <collei@collei.com.br>
 *	@author Alarido <alarido.su@gmail.com>
 *	@since 2022-08-08
 */
class NominalCase extends ColleiEnum
{
	/**
	 *	@const array ALLOWED
	 */
	public const ALLOWED = [
		'Nominative', 'Accusative', 'Genitive', 'Dative',
		'Ablative', 'Locative', 'Instrumental', 'Partitive',
		'Abessive', 'Comitative'
	];

}

