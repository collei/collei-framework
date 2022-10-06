<?php
namespace Collei\App\Events;

use Collei\Events\Event;
use Collei\Events\EventInterface;
use Collei\App\App;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Parent class for events with arbitrary data.
 */
class AppEvent extends Event implements EventInterface
{
	private $context = null;

	public function __construct(App $context)
	{
		$this->context = $context;
	}

	public function getContext(): App
	{
		return $this->context;
	}

}

