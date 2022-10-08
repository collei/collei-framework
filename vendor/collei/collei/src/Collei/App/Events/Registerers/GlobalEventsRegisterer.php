<?php
namespace Collei\App\Events\Registerers;

use Collei\App\Events\AppListenerProvider;
use Collei\App\Seekers\ClassSeeker;
use ReflectionMethod;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-07
 *
 *	Performs scan and registration of any declared event listeners.
 */
class GlobalEventsRegisterer
{
	/**
	 *	Performs scan and registration of any declared event listeners.
	 *
	 *	@return	void
	 */
	public static function scanForListeners(): void
	{
		$alp = AppListenerProvider::getInstance();
		//
		$classes = ClassSeeker::scan(
			PLAT_GROUND . DIR_SEP . PLAT_SITES_LISTENERS_FOLDER
		);
		//
		if ($classes === false) {
			return;
		}
		//
		foreach ($classes as $class => $path) {
			require_once $path;
			$listenerInstance = new $class();
			$eventName = '';
			//
			if (is_callable($listenerInstance)) {
				$refunc = new ReflectionMethod($listenerInstance, '__invoke');
				$rearg = $refunc->getParameters();
				//
				if (!empty($rearg)) {
					$eventName = (string)($rearg[0]->getType());
				}
				//
				if (!empty($eventName)) {
					$alp->addListener($eventName, $listenerInstance);
				}
			}
		}
	}

}

