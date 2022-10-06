<?php
namespace Collei\App\Events;

use Collei\App\Events\AppEvent;
use Collei\Events\EventInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Happens before app run.
 */
class BeforeRunEvent extends AppEvent implements EventInterface
{
}
