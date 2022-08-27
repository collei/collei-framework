<?php

namespace App\Commands;

use Collei\Console\CommandLine;
use Collei\Console\Commands\Command;
use Collei\Console\Output\Rich\Formatter;
use Collei\Console\Co;

use Collei\Utils\Str;
use ColleiLang\Engine as CLE;


/**
 *	This creates a Command and it gets inserted in the Cyno CLI Engine.
 *
 *
 */
class VerbConjugator extends Command
{
	/**
	 *	@var string
	 *
	 *	Define here your command signature
	 */
	protected $signature = "conjugate {verb}";

	/**
	 *	@var string
	 *
	 *	Define here your command help (a brief version)
	 */
	protected $help = "Syntax: conjug [verb]";

	/**
	 *	@var string
	 *
	 *	Define here your command help (the long version)
	 */
	protected $longHelp = "Conjugates Collei verbs.
		<fg=yellow>verb</>	the infinitive form of verb.
	";

	/**
	 *	Entry point of your command line
	 *
	 *	@param	CommandLine	$com
	 *	@return	mixed
	 */
	public function handle(CommandLine $com)
	{
		try {
			$this->perform($term = $this->argument('verb', 'null'));
		} catch (Exception $ex) {
			$this->warn("$term is not a valid infinitive Verb.");
		}
	}

	private function perform(string $term)
	{
		$verb = CLE::createVerb($term);
		$persons = CLE::persons();
		$tenses = CLE::tenses();
		$modes = CLE::modes();
		$voices = CLE::voices();
		$defines = CLE::definitenesses();
		//
		foreach ($modes as $mode) {
			if ($mode->is('Imperative')) {
				$line = ' * ' . Str::pad($mode . ' ' . $voice . ' ' . $tense . ' ' . $define, 70);
				$this->warn($line);
				foreach ($persons as $person) {
					$line = '    ';
					foreach ($defines as $define) {
						if ($person->in('Mi','On','Onk')) {
							$line .= Str::pad("   ----", 35);
						} else {
							$form = $verb->conjugate($person, $tense, $mode, $voice, $define);
							$line .= Str::pad($person . ' ' . $form, 36);
						}
					}
					$this->info($line);
				}
			} else {
				foreach ($voices as $voice) {
					foreach ($tenses as $tense) {
						$line = ' * ' . Str::pad($mode . ' ' . $voice . ' ' . $tense, 70);
						$this->warn($line);
						foreach ($persons as $person) {
							$line = '    ';
							foreach ($defines as $define) {
								$form = $verb->conjugate($person, $tense, $mode, $voice, $define);
								$line .= Str::pad($person . ' ' . $form, 36);
							}
							$this->info($line);
						}
					}
				}
			}
		}
	}

}
