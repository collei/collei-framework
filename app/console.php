<?php

use Collei\Console\Cyno;
use Collei\Console\Co;
use Collei\Console\Output\RGBColor;
use Collei\Utils\Str;

//use Exception;
//use Throwable;

use Collei\System\Process;

use Collei\Auth\Ldap\Ldap;

use Collei\Utils\Values\TextStream;

use Collei\Console\Output\OutputFormat;
use Collei\Console\Output\Grids\Table;
use Collei\Geometry\Point;

use Collei\Geometry\Rect;
use Collei\Console\Output\Rich\Formatter;

use Collei\System\Sockets\Socket;
use Collei\System\Sockets\TinyServerSocket;


function generateWorkerLocalAddress()
{
	static $lastIPAddrInt = 0x7F000002;
	//
	do {
		$next = ++$lastIPAddrInt;
	} while (($next & 0x000000FF) == 0 || ($next & 0x000000FF) == 255);
	//
	return implode('.', [
		($next & 0x7F000000) >> 24,
		($next & 0x00FF0000) >> 16,
		($next & 0x0000FF00) >> 8,
		($next & 0x000000FF)
	]);
}

Cyno::command('lick {alfa=1} {bravo=2} {charlie=3} {delta=4} {echo=5}', function(){
	$nomes = ['alfa','bravo','charlie','delta','echo'];
	$jsonitems = [];
	foreach ($nomes as $nome) {
		$valor = $this->argument($nome);
		$jsonitems[] = "\"$nome\": \"$valor\"";
	}
	$json = '{' . implode(',', $jsonitems) . '}';
	//
	$port = 9876;
	$message = $json;
	$this->write("[sent?] $message\r\n");
	//
	$soc = (new Socket(AF_INET, SOCK_STREAM, 0))->connect('127.1.0.2', $port);
	$soc->write("$message\r\n");
	$soc->close();
});


Cyno::command('licl {port=9876} {msg}', function(){
	$port = (int)$this->argument('port');
	$message = $this->argument('msg');
	$this->write("[sent?] $message\r\n");
	//
	$soc = (new Socket(AF_INET, SOCK_STREAM, 0))->connect('127.1.0.2', $port);
	$soc->write("$message\r\n");
	$soc->close();
});

Cyno::command('liserv {port=9876}', function(){
	$port = (int)$this->argument('port');
	// create a streaming socket, of type TCP/IP
	$sock = socket_create(AF_INET, SOCK_STREAM, 0);
	// set the option to reuse the port
	socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
	socket_set_nonblock($sock);
	// "bind" the socket to the address to "localhost", on port $port
	// so this means that all connections on this port are now our resposibility to send/recv data, disconnect, etc..
	socket_bind($sock, '127.1.0.2', $port);
	// start listen for connections
	socket_listen($sock);
	// create a list of all the clients that will be connected to us..
	// add the listening socket to this list
	$clients = array($sock);
	//
	while (true) {
		// create a copy, so $clients doesn't get modified by socket_select()
		$read = $clients;
		$write = $except = NULL;
		//
		// notify server monitors
		$this->write("<fg=blue>[time]</> <fg=cyan>" . date('Y-m-d H:i:s') . "</>\r\n");
		// get a list of all the clients that have data to be read from
		// if there are no clients with data, go to next iteration
		if (socket_select($read, $write, $except, 1) < 1) {
			continue;
		}
		// check if there is a client trying to connect
		if (in_array($sock, $read)) {
			// accept the client, and add him to the $clients array
			$clients[] = $newsock = socket_accept($sock);
			// send the client a welcome message
			//socket_write($newsock, "no noobs, but ill make an exception :)\nThere are ".(count($clients) - 1)." client(s) connected to the server\n");
			socket_getpeername($newsock, $ip);
			$this->write("<fg=cyan>[info]</> <fg=yellow>New client connected: {$ip}</>\r\n");
			// remove the listening socket from the clients-with-data array
			$key = array_search($sock, $read);
			unset($read[$key]);
		}
		// loop through all the clients that have data to read from
		foreach ($read as $read_sock) {
			$content = '';
			$bytes = 0;
			socket_set_nonblock($read_sock);
			// reals all data, recursively if needed
			while (true) {
				$data = '';
				$bytes = @socket_recv($read_sock, $data, 1024, 0);
				$content .= trim($data);
				// exit loop if no more data
				if ((0 === $bytes) || (false === $bytes)) {
					break;
				}
			}
			//
			// remove client for $clients array
			$key = array_search($read_sock, $clients);
			unset($clients[$key]);
			$this->write("<fg=cyan>[info]</> <fg=yellow>client disconnected.</>\n");
			// 
			if (!empty($content)) {
				$this->write("<fg=blue>[client]</> <fg=white>$content</>\r\n");
				break;
			}
		} // end of reading foreach
	}
	// close the listening socket
	socket_close($sock);
});


Cyno::command('shout {message} {port=2999} {addr=127.0.1.1}', function(){
	$address = $this->argument('addr');
	$port = (int)$this->argument('port');
	$message = $this->argument('message');
	//
	$soc = (new Socket(AF_INET, SOCK_STREAM, 0))->connect($address, $port);
	$soc->write($message);
	$soc->close();
});

Cyno::command('listen {port=2999} {addr=127.0.1.1}', function(){
	$address = $this->argument('addr');
	$port = (int)$this->argument('port');
	(new TinyServerSocket(AF_INET, SOCK_STREAM, 0))
		->bind($address, $port)
		->loop(function($client){
			$input = '';
			//
			if ($client->read($input) !== false) {
				$this->write("<fg=lime>rcvd2:</> <fg=yellow>$input</>\r\n");
				//
				return (trim($input) === 'exit');
			} else {
				echo "\r\nnoreadingcoz ???";
				return true;
			}
		});
});



Cyno::command('tinker', function(){
	$expr_full = '';
	//
	while ($expr = $this->ask('>')) {
		$expr = trim($expr);
		//
		if ($expr == 'exit') {
			break;
		} elseif (\substr($expr, -1) == '\\') {
			$expr_full .= \substr($expr, 0, -1);
			$expr = '';
			continue;
		}
		//
		$expr_full .= $expr;
		$expr = '';
		if (\substr($expr_full, -1) != ';') {
			$expr_full .= ';';
		}
		$expr_full = 'return ' . $expr_full;
		//
		$autoloader = 'require_once PLAT_GROUND . "/vendor/autold.php"; ';
		eval($autoloader);
		try {
			$result = eval($expr_full);
		} catch (Exception $tr) {
			$result = $tr;
		} catch (Throwable $tr) {
			$result = $tr;
		}
		//
		$expr_full = '';
		//
		if (\is_numeric($result) || \is_bool($result) || \is_string($result)) {
			$type = \gettype($result);
			$this->write("<fg=yellow>{$type}(" . var_export($result, true) . ")</>\r\n");
		} elseif (\is_array($result)) {
			$this->write('<fg=yellow>' . var_export($result, true) . "</>\r\n");
		} elseif ($result instanceof Throwable || $result instanceof Exception) {
			$msg = $result->getMessage() . "\r\n" . $result->getTraceAsString();
			$this->write('<fg=light-red>' . $msg . '</>');
		} else {
			$this->write('<fg=yellow>' . print_r($result, true) . '</>');
		}
		//
		echo " \r\n";
	}
});


Cyno::command('square {left=20} {top=10} {width=40} {height=5} {text=coisas}', function(){
	$text = 'Texto Normal <fg=#ff0000>Texto Vermelho</> Texto Normal <fg=#00ff00>Texto Verde</> Texto Normal <options=reverse>Revertido</> <fg=#0000ff>Texto Azul</> Texto Normal <fg=#ffff00>Texto Amarelo</> Normal <fg=#ff00ff>Texto <options=underline>Sublinhado</> <fg=#ff00ff>Rosa Shock</> Texto Normal <fg=#0000ff>Texto Azul</> Texto Normal <fg=#ffff00>Texto Amarelo</> Normal <fg=#ff00ff>Texto <options=underline>Sublinhado</> <fg=#ff00ff>Rosa Shock</> Texto Normal <fg=#00ffff>Texto Verde Agua <options=bold>Com Negrito</> Sem Negrito</> Texto Normal';
	//$text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut non justo vel massa ullamcorper vestibulum. Nulla facilisi. Nam eget nisi et mi laoreet iaculis vel eu tortor. Curabitur euismod turpis at tellus bibendum hendrerit. Phasellus lobortis nec nisl ac tristique. In in lacus eu neque lobortis convallis. Curabitur ut lorem eget nisl egestas bibendum in non turpis. Nulla convallis ornare quam, eget ullamcorper felis posuere at. Maecenas rutrum ultrices arcu, ut egestas massa iaculis a. Praesent vitae consequat lorem. Morbi interdum massa vel enim pretium, nec scelerisque turpis mattis. Pellentesque ac elit vitae diam porta sollicitudin.';
	$rect = Rect::new(
		(int)$this->argument('left', 20),
		(int)$this->argument('top', 10),
		(int)$this->argument('width', 40),
		(int)$this->argument('height', 5)
	);
	$this->writeIn($rect, $text);
});



Cyno::command('table {rows=3} {cols*}', function(){
	//
	$rows = $this->argument('rows');
	$cols = $this->argument('cols');
	$colCount = count($cols);
	//
	$table = new Table($cols, Point::new(3, 3), $colCount * 15, 1, OutputFormat::fromTag('mine','<fg=#00ffff;bg=#000033>'));
	$table->setFormat(OutputFormat::fromTag('mine','<fg=#ffff00;bg=#330000>'));
	$ce = 1;
	//
	for ($yy = 0; $yy < $rows; $yy++) {
		$table->addRow();
		for ($xx = 0; $xx < $colCount; $xx++) {
			$table->setCell($yy+1, $xx+1, "cell #$ce");
			++$ce;
		}
	}
	//
	Co::clearScreen();
	$table->render();
	//
});


Cyno::command('testpad {wide=25} {--left} {--center} {--right}', function(){
	//
	$wide = $this->argument('wide');
	//
	if ($this->option('right')) {
		$align = 1;
	} elseif ($this->option('center')) {
		$align = 0;
	} else {
		$align = -1;
	}
	//
	$phrases = [
		'For the long trailing tread upon the stars, The Little Prince finally reached his home.',
		'The problem is human.',
		'Something went wrong at Denmark Kingdom.',
		'The sons of the men went before Childe, but Signora deraised them.',
		'Beware of the dog!',
		'Perhaps the traveler\'s sister has died a long, loooong time ago.',
		'For the times of dialup connection! Geooorge!'
	];
	//
	echo "Size: $wide, Align: $align\r\n";
	//
	foreach ($phrases as $phrase) {
		$plen = strlen($phrase);
		echo "\r\n:($plen)\t" . $phrase;
	}
	//
	echo "\r\n";
	//
	foreach ($phrases as $phrase) {
		$plen = strlen($phrase);
		echo "\r\n:($plen)\t" . Str::pad($phrase, $wide, $align);
	}
	//
	$this->info("\r\n\r\n");
});


/*
 *	adjust values for everyone
 */
Cyno::command('sete {varname?} {varval?}', function(){
	//
	$vna = $this->argument('varname');
	$val = $this->argument('varval');
	//
	$this->setEnv($vna, $val);
	//
});


/*
 *	show all adjusted values everyone did
 */
Cyno::command('liscom {what?}', function(){
	//
	$prefix = $this->argument('what');
	$items = Cyno::available($prefix);
	$cols = [25, 25, 25];
	//
	while (!empty($items)) {
		$line = "	";
		foreach ($cols as $col) {
			if ($text = \array_shift($items)) {
				$line .= Str::pad($text, $col);
			}
		}
		$this->info($line);
	}
	//
});


/*
 *	show all adjusted values everyone did
 */
Cyno::command('gete {varname?}', function(){
	//
	$vna = $this->argument('varname');
	//
	if (!empty($vna)) {
		$this->info($this->getEnv($vna) ?? '');
	} else {
		$all = $this->listEnvWithValues();
		//
		foreach ($all as $n => $v) {
			$this->write("<fg=green>$n</>: <fg=yellow>$v</> \r\n");
		}
	}
	//
});


/*
 *	demonstrates keyboard hidden input
 */
Cyno::command('eula {vengeance?}', function(){
	//
	$login = trim($this->ask('Seu login'));
	$senha = trim($this->secret('Sua senha'));
	//
	$this->write('- DSN: MYSQL:user=<fg=yellow>'.$login.'</>;pwd=<fg=red>'.$senha.'</>;server=localhost;database=lavi;preempt=true');
	//
});


/*
 *	demonstrate console color capabilities
 */
Cyno::command('raiden-shogun {euthymia=effectphrase}', function(){
	//
	$letras = str_split("0123456789ABCDEF");
	$phrase = $this->argument('euthymia');
	//
	if ($phrase == 'effectphrase') {
		$i = 0;
		//
		foreach ($letras as $r) {
			foreach ($letras as $g) {
				foreach ($letras as $b) {
					$color = $r . $g . $b;
					//
					$this->write("<fg=#{$color}> #{$color}</>");
					//
					++$i;
					if ($i % 16 == 0) {
						$this->newLine();
					}
				}
				//
				usleep(6250);
			}
		}
	} else {
		$phrase = substr(trim($phrase),0,70);
		//
		foreach ($letras as $r) {
			foreach ($letras as $g) {
				foreach ($letras as $b) {
					$color = '#' . $r . $g . $b;
					//
					$this->line("<fg={$color}>COLOR {$color} {$phrase}</>");
				}
				usleep(1250);
			}
		}
	}
});


/*
 *	demonstrates parser
 */
Cyno::command('test-call {phrase} {--quiet}', function(){
	$phrase = $this->argument('phrase');
	//
	if ($this->option('quiet')) {
		$this->write(
			'Called <fg=yellow>raiden-shogun "'
			. $phrase
			. '"</> silently. Let\'s wait a bit.'
		);
		$this->callSilently('raiden-shogun "' . $phrase . '"');
	} else {
		$this->call('raiden-shogun "' . $phrase . '"');
		$this->write(
			'Called <fg=yellow>raiden-shogun "'
			. $phrase
			. '"</> with full of its output.'
		);
	}
	$this->write("\r\n");
});


/*
 *	adjust colors
 */
Cyno::command('color {red=0} {green=0} {blue=0}', function(){
	$red = (int)$this->argument('red');
	$gre = (int)$this->argument('green');
	$blu = (int)$this->argument('blue');
	//
	$res = RGBColor::rgbToRGBI($red, $gre, $blu);
	//
	$this->info(print_r([[$red, $gre, $blu], $res], true));
});


/*
 *	test progress counter
 */
Cyno::command('progress {count}', function(){
	$count = (int)trim($this->argument('count'));
	//
	for ($i=0; $i<$count; $i++) {
		echo "\x00\x1b[3D";
		$this->info(" [ " . $i . " %] ");
		sleep(1);
	}
});


/*
 *	test color hex
 */
Cyno::command('colorhex {hexco}', function(){
	$hex = trim($this->argument('hexco'));
	$res = RGBColor::hexToRGBI($hex);
	//
	$this->info(print_r([$hex, $res], true));
});


/*
 *	test command options in and outside
 */
Cyno::command(
	'barbershop {service=haircut} {style=military} {grade=1}',
	function() {
		$service = $this->argument('service');
		$style = $this->argument('style', 'military');
		$grade = $this->argument('grade');
		//
		$grade = (is_numeric($grade) && ($grade >= 1) && ($grade <= 3)) ? $grade : 0;
		$options = ['Basic','Executive','Premium'];
		//
		if ($grade == 0) {
			$result = $this->choice("Select a service grade", $options);
			$result = $options[$result];
		} else {
			$result = $options[$grade - 1];
		}	
		//
		$this->write(
			"\r\nDoing a <fg=light-green>$result</> <fg=cyan>$style</> <fg=yellow>$service</> for you.\r\n"
		);
	}
);


Cyno::command('moveby {word=HORA}', function(){
	$word = $this->argument('word') ?? 'heizou';
	$slen = strlen($word);
	//
	if ($slen > 20) {
		$slen = 20;
		$word = substr($word, 0, $slen);
	}
	//
	$ho = 5;
	$hores = [];
	//
	while (true) {
		$hores[] = $ho;
		$ho += $slen;
		if ($ho > 79) {
			break;
		}
	}
	//
	$this->info('Wait 2 second...');
	//
	$vertes = [2,4,6,8,10,12,14,16,19,17,15,13,11,9,7,5,3];
	$dulcora = ['red','green','blue'];
	$dulc = 0;
	sleep(2);
	//
	foreach ($hores as $ho) {
		foreach ($vertes as $ve) {
			usleep(250000);
			Co::moveTo($ho, $ve);
			$dulc = $dulc == 2 ? 0 : $dulc + 1;
			$color = $dulcora[$dulc];
			$this->write("<fg=$color>$word</>");
		}
	}
	//
	sleep(1);
});


Cyno::command('xadrez {word=HORA}', function(){
	$word = $this->argument('word') ?? 'heizou';
	$word = substr($word,0,5);
	//
	$this->info('Starting...');
	sleep(1);
	Co::clearScreen();
	//
	$vertes = [10,11,12,13,14,15,16,17];
	$hores = [5,7,9,11,13,15,17,19];
	$cores = ['blue','red'];
	$cor = 'blue';
	$either = true;
	//
	foreach ($hores as $ho) {
		foreach ($vertes as $ve) {
			usleep(6250);
			$cor = $cores[($either ? 1 : 0)];
			$either = !$either;
			$this->writeTo($ho, $ve, "<fg=$cor>██</>");
		}
		$either = !$either;
	}
	//
	$this->writeTo(30, 23, '<fg=blue>Esta linha é adicional àquela passada no esquema. Se entende, deixe o joinha.</>');
	$this->writeTo(30, 22, '<fg=light-blue>Esta linha é adicional àquela passada no esquema. Se entende, deixe o joinha.</>');
	$this->writeTo(30, 21, '<fg=magenta>Esta linha é adicional àquela passada no esquema. Se entende, deixe o joinha.</>');
	//
	sleep(1);
	Co::moveTo(68, 21);
	sleep(1);
	Co::clearLine(23);
	sleep(1);
});

Cyno::command('eldap {user=Administrator}', function(){
	$user = $this->argument('user');
	$serv = 'ldap://cyno.collei.com.br';
	$tree = 'CN=Users,DC=collei,DC=com,DC=br';
	//
	$adm_user = trim($this->ask('Administrator username (something like DOMAIN\\username)'));
	$adm_pass = trim($this->secret('His/her password'));
	//
	$ldap = new Ldap($serv);
	$ldap->bind($adm_user, $adm_pass);
	$data = $ldap->search("(sAMAccountName={$user})", $tree);
	//
	$this->write('<fg=yellow>' . print_r(['user'=>$user,'ldap'=>$data], true) . '</>');
});


