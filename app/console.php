<?php

use Collei\Console\Cyno;
use Collei\Console\Co;
use Collei\Console\Output\RGBColor;
use Collei\Utils\Str;

//use Exception;
//use Throwable;

use Collei\System\Process;

use App\Commands\PackinstCommander;
use Collei\Auth\Ldap\Ldap;

use Collei\Utils\Values\TextStream;

use Collei\Console\Output\OutputFormat;
use Collei\Console\Output\Grids\Table;
use Collei\Geometry\Point;

use Collei\Geometry\Rect;
use Collei\Console\Output\Rich\Formatter;



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


Cyno::command('testream {wide=25} {--wrap}', function(){
	//
	$this->write("testando:\r\n\r\n");
	$wrap = $this->option('wrap');
	$wide = $this->argument('wide');
	//
	$texto = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut non justo vel massa ullamcorper vestibulum. Nulla facilisi. Nam eget nisi et mi laoreet iaculis vel eu tortor. Curabitur euismod turpis at tellus bibendum hendrerit. Phasellus lobortis nec nisl ac tristique. In in lacus eu neque lobortis convallis. Curabitur ut lorem eget nisl egestas bibendum in non turpis. Nulla convallis ornare quam, eget ullamcorper felis posuere at. Maecenas rutrum ultrices arcu, ut egestas massa iaculis a. Praesent vitae consequat lorem. Morbi interdum massa vel enim pretium, nec scelerisque turpis mattis. Pellentesque ac elit vitae diam porta sollicitudin.';
	//
	$ts = new TextStream($texto);
	$i = 1;
	//
	while (!$ts->eof()) {
		$read = $ts->read($wide, $wrap);
		$slen = strlen($read);
		echo "\r\n$i\t($slen)\t[" . $read . "]";
		++$i;
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
		$line = "    ";
		foreach ($cols as $col) {
			if ($text = \array_shift($items)) {
				$line .= Str::pad($text, $col);
			}
		}
		$this->info($line);
	}
	//
	//Co::writeInRect(40,5,70,9,"Este é um texto longo demais para caber neste quadradinho, mas isto é apenas um teste para tentarmos fazer uma interface legal de programação para escrever no console.");
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
 *	demonstrates homonyme commands
 */
Cyno::command('packint {verb?} {repo?}', function(){
	//
	$whatdo = $this->argument('verb');
	$gitrepo = $this->argument('repo');
	//
	$this->write(
		'- I\'ll <fg=yellow>'
		. $whatdo
		. '</> the <fg=cyan>'
		. $gitrepo
		. '</> to the local project !'
		. "\r\n\r\n"
	);
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


Cyno::command('mercado {alfa} {--bravo} {-C|--charlie} {delta : detran} {-e|--echo=*} ', function(){
	$this->write(".......\r\n<fg=yellow>" . print_r($this, true) . "</>\r\n.......");
	$this->error('Berrante Dourado');
});


Cyno::command('palestra {pais} {cidades*} {--left} {--right} {--center} {-a|--aeroportos=*}', function(){
	$inf = [
		'options' => $this->options(),
		'arguments' => $this->arguments()
	];
	//
	$this->write(
		".......\r\n<fg=yellow>" . print_r($inf, true) . "</>\r\n......."
	);
});


Cyno::command('mail:send {user*} {--log=}', function(){
	$this->write(
		".......\r\n<fg=yellow>" . print_r($this, true) . "</>\r\n......."
	);
	//
	echo "\r\nusuarios.count=" . $this->argumentCount('user');
	echo "\r\nusuarios=" . print_r($this->argument('user'), true);
	echo "\r\n--log=" . $this->option('log');
});


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
	$cores = ['green','red'];
	$cor = 'green';
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


Cyno::command('csp {wait=yes}', function(){
	$texto = Process::quickRead('mode con');
	$sttya = Process::quickRead('stty 2>&1');
	$largo = [];
	$ansicon = '120x9699 (80x24)';
	$ansicon2 = '80x24';
	//
	preg_match('/--------+\\r?\\n.+?(\\d+)\\r?\\n.+?(\\d+)\\r?\\n/i', $texto, $largo);
	preg_match('/^(\\d+)x(\\d+)(\\s*\\((\\d+)x(\\d+)\\))?$/', $ansicon, $video);
	preg_match('/^(\\d+)x(\\d+)(\\s*\\((\\d+)x(\\d+)\\))?$/', $ansicon2, $video2);
	//
	$resultados = [
		'texto' => [$texto, $largo],
		'ansicon' => [$ansicon, $video],
		'ansicon2' => [$ansicon2, $video2],
		'sttya' => [$sttya],
		'env' => $_ENV,
	];
	//
	echo "\r\n---W-i-n----------------------------\r\n";
	$this->write('<fg=yellow>' . print_r($resultados, true) . '</>');
	echo "\r\n------♥----- -----------------------\r\n";
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


