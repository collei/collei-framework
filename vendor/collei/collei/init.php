<?php

/**
 *	Register plugin engine and version
 */
plat_plugin_register([
	'plugin' => 'collei/plat',
	'description' => 'Collei Plat MVC Platform and Framework',
	'version' => '0.9.1',
	'dependencies' => [
		'collei/packinst' => '*',
		'collei/dately' => '*',
		'Bacon/BaconQrCode' => '2.0.7',
		'DASPRiD/Enum' => '1.0.3',
		'endroid/qr-code' => '4.0.0',
		'khanamiryan/php-qrcode-detector-decoder-master' => '^1.0.5',
		'sonata-project/GoogleAuthenticator' => '2.3.1',
	],
	'classes_folder' => 'src',
]);

