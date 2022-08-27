<?php
namespace ColleiLang\Morphology;

use ColleiLang\Morphology\Term;

/**
 *	Embodies Noun features and actions 
 *
 *	@author Collei Inc. <collei@collei.com.br>
 *	@author Alarido <alarido.su@gmail.com>
 *	@since 2022-08-08
 */
class NominalTerm extends Term
{
	
	public function decline(Number $number, NominalCase $case)
	{
		return Declensor::decline(
			$this, $number, $case
		);
	}
	
	public function possessive(Person $person, Number $number)
	{
		return Declensor::declinePossessive(
			$this, $person, $number
		);
	}

}

