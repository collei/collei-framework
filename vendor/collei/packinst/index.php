<?php

include 'src\Packinst\Utils\ArrayTokenScanner.php';
include 'src\Packinst\Package\GitPackage.php';
include 'src\Packinst\Package\GithubPackage.php';
include 'src\Packinst\Package\PackageManager.php';
include 'src\Packinst\Package\Downloader\GitPackageDownloader.php';
include 'src\Packinst\Package\Installer\GitPackageInstaller.php';

use Packinst\Package\GithubPackage;
use Packinst\Package\PackageManager;

PackageManager::setLocation(realpath('vendor'));

$infos = PackageManager::getInstalledPackages(true);

$nl = "\r\n";

$git_package = $_REQUEST['git_package'] ?? '';
$git_action = $_REQUEST['git_action'] ?? '';


?>
<!doctype html>
<html>
<head>
	<style>
#divided {
	white-space: nowrap !important;
	width: 97.5%;
}
#divided fieldset {
	vertical-align: top !important;
	display: inline-block !important;
	height: 160px;
	margin: 0;
}
#divided fieldset.s20 {
	min-width: 17.5% !important;
	max-width: 17.5% !important;
}
#divided fieldset.s40 {
	min-width: 40% !important;
	max-width: 40% !important;
}
#logbelow {
	max-height: 70vh !important;
}
.autosiz {
	overflow-x: scroll !important;
	overflow-y: scroll !important;
}

	</style>
	<script>
function showside(sel)
{
	let pd = sel.options[sel.selectedIndex].getAttribute('datapack');
	let display = document.getElementById('showsider');
	display.innerHTML = pd;
}
	</script>
</head>
<body>
<hr>
<div id="divided">
	<fieldset class="s20">
		<form action="./" method="post">
			<input type="hidden" name="git_action" value="install">
			<p>
				In order to INSTALL a package<br>
				from GITHUB, please inform the repository<br>
				in format <b>groupname/projectname</b><br>
				in the field below<br>
				and then hit <b>INSTALL</b>.
			</p>
			<p>
				<input type="text" name="git_package" />
				&nbsp; &nbsp;
				<input type="submit" name="git_package_installer" value="INSTALL" />
			</p>
		</form>
	</fieldset>
	<fieldset class="s20">
		<form action="./" method="post">
			<input type="hidden" name="git_action" value="uninst">
			<p>
				In order to UNINSTALL a package,<br>
				please choose the repository<br>
				you want to remove<br>
				and then hit <b>UNINSTALL</b>.
			</p>
			<p>
				<select name="git_package" onchange="showside(this);"><?php
foreach ($infos as $n => $v)
{
	?>					<option value="<?=($n)?>" datapack="<?=(print_r($v,true))?>"><?=($n)?></option><?=("\r\n")?><?php
}
				?>				</select>
				&nbsp; &nbsp;
				<input type="submit" name="git_package_installer" value="UNINSTALL" />
			</p>
		</form>
	</fieldset>
	<fieldset class="s20">
		<form action="./" method="post">
			<input type="hidden" name="git_action" value="update">
			<p>
				In order to UPDATE a package, please<br>
				choose which you want to remove<br>
				in the field below<br>
				and then hit <b>UPDATE</b>.
			</p>
			<p>
				<select name="git_package" onchange="showside(this);"><?php
foreach ($infos as $n => $v)
{
	?>					<option value="<?=($n)?>" datapack="<?=(print_r($v,true))?>"><?=($n)?></option><?=("\r\n")?><?php
}
				?>				</select>
				&nbsp; &nbsp;
				<input type="submit" name="git_package_installer" value="UPDATE" />
			</p>
		</form>
	</fieldset>
	<fieldset class="s40 autosiz">
		<pre id="showsider"><?=(print_r(reset($infos),true))?></pre>
	</fieldset>
</div>
<hr>
<div id="logbelow" class="autosiz">
	<pre>
<?php

################################################################
####	my own practice workspace, also serves as example	####
################################################################

function install_package($packageName)
{
	list($group, $project) = explode('/', $packageName);
	//
	$gp = new GithubPackage($group, $project);
	$gp->fetchRepositoryInfo();
	//
	return PackageManager::install($gp);
}

function remove_package($packageName)
{
	return PackageManager::remove($packageName);
}

function update_package($packageName)
{
	return PackageManager::update($packageName);
}

if (!empty($git_package) && !empty($git_action))
{
	if (preg_match('/([\w_\-.]+)[\\/]([\w_\-.]+)/', $git_package))
	{
		echo '<fieldset>' . print_r($git_package, true) . '</fieldset>' . $nl;
		//
		if ($git_action == 'install')
		{
			if (install_package($git_package))
				echo "- Package $git_package installed successfully. $nl";
			else
				echo "- Error occurred while installing $git_package. Please verify. $nl";
		}
		elseif ($git_action == 'uninst')
		{
			if (remove_package($git_package))
				echo "- Package $git_package removed successfully. $nl";
			else
				echo "- Error occurred while removing $git_package. Please verify. $nl";
		}
		elseif ($git_action == 'update')
		{
			if (update_package($git_package))
				echo "- Package $git_package updated successfully. $nl";
			else
				echo "- $git_package is already updated or maybe not installed. Please verify. $nl";
		}
	}
	else
	{
		echo "- Invalid package: <b>$git_package</b> $nl";
	}
}
elseif (1 == 2)
{
	echo '<hr>';
	$col = new GithubPackage('collei/plato');
	$col->fetchRepositoryInfo();
	$found = $col->repositoryExists() ? 'existe' : 'não existe';
	echo "<fieldset><legend>$found</legend>" . print_r($col->repositoryInfo, true) . '</fieldset>';

	echo '<hr>';
	$edd = new GithubPackage('endroid/qr-code');
	$edd->fetchRepositoryInfo();
	$found = $edd->repositoryExists() ? 'existe' : 'não existe';
	echo "<fieldset><legend>$found</legend>" . print_r($edd->repositoryInfo, true) . '</fieldset>';
}

$verificam = ['collei/packinst', 'endroid/calendar', 'Bacon/BaconQrCode'];

foreach ($verificam as $verific)
{
	$status = PackageManager::checkPluginState($verific);
	$message = PackageManager::PS_MESSAGE[$status] ?? '???';
	//
	if ($status == PackageManager::PS_UPDATED)
		echo "<span style='color:#080;'>Plugin $verific: $message.</span>\r\n"; 
	elseif ($status == PackageManager::PS_OUTDATED)
		echo "<span style='color:#F00;'>Plugin $verific: $message</span>\r\n"; 
	else
		echo "<span style='color:#444;'>Plugin $verific: $message</span>\r\n"; 
}


?>
	</pre>
</div>
</body>
</html>
