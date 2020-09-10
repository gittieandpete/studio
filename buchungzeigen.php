<?php
$titel = "Buchung zeigen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php'); ?> 

<h2><?php print $titel;?></h2>

<?php logincheck();

if ($_SESSION['login'] == 1)
	{
if (!isset($_SESSION['buchung']))
	{
	$_SESSION['buchung'] == 'nichtausgefuehrt';
}

if ($_SESSION['buchung'] != 1)
	{
	$halbestunde = $_SESSION['halbestunde'];
	$stunde = $_SESSION['stunde'];
	$tag = $_SESSION['tag'];
	$monat = $_SESSION['monat'];
	$jahr = $_SESSION['jahr'];
	$bishalbestunde = $_SESSION['bishalbestunde'];
	$bisstunde = $_SESSION['bisstunde'];
	$bistag = $_SESSION['bistag'];
	$bismonat = $_SESSION['bismonat'];
	$bisjahr = $_SESSION['bisjahr'];
	$buchungsbeginn = $_SESSION['buchungsbeginn'];
	$buchungsende = $_SESSION['buchungsende'];
	$wochentag = $_SESSION['wochentag'];
	$biswochentag = $_SESSION['biswochentag'];
	$anfang = date('d.F Y H:i', $buchungsbeginn);
	$ende = date('d.F Y H:i', $buchungsende);
	$dauer = $buchungsende - $buchungsbeginn;
	$anzahlstunden = floor($dauer/3600);
	$anzahlminuten = sprintf("%02d", ($dauer % 3600)/60);
}

if (iswech())
	{
	if ($fehler = validiere_buchungsformular())
		{
		zeige_buchungsformular($fehler);
	} else {
		verarbeite_buchungsformular();
	}
} else {
	zeige_buchungsformular();
}

} // logincheck

fehlersuche ($_POST);
fehlersuche ($_SESSION);
require('includes/footer.php');

// Functions for the formula
function zeige_buchungsformular($fehler='')
	{
	global $wochentag, $anfang, $biswochentag, $ende, $anzahlstunden, $anzahlminuten;
	if ($fehler) { ?> 
		<ul class='meldung'>
			<li><?php print implode("</li>\n\t<li>",$fehler);?></li>
		</ul>
	<?php }  ?> 
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
		<legend>Buchung ausführen</legend>
    <table>
        <tr><td>Buchungsbeginn</td><td><?php print $wochentag;?>, <?php print $anfang;?></td></tr>
        <tr><td>Buchungsende</td><td><?php print $biswochentag;?>, <?php print $ende;?></td></tr>
        <tr><td>Dauer</td><td><?php print $anzahlstunden;?>:<?php print $anzahlminuten;?> Stunden</td></tr>
		<tr>
	<?php // $feldname, $colspan, $label
	input_submit('ok','1','ok'); ?> 
		</tr>
	</table>
	</fieldset>
	<?php input_hidden(); ?> 
	</form>
<?php }

function validiere_buchungsformular()
	{
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	fehlersuche($_POST);
	if ($_POST['ok'] != 'ok')
		{
		$fehler[] = 'Bitte bestätige die Buchung mit dem OK-Button!';
	}
	if (!isset($_SESSION['login']))
		{
		$fehler[] = 'Bitte logge dich ein!';
	}
	if ($_SESSION['buchung'] == 1)
		{
		$fehler[] = 'Bitte neue Buchungszeiten auswählen.';
	}
	if (isset($_SESSION['login']))
		{
		if ($_SESSION['login'] != 1)
			{
			$fehler[] = 'Bitte logge dich ein!';
		}
	}
	return $fehler;
}

function verarbeite_buchungsformular()
	{
	global $wochentag, $anfang, $biswochentag, $ende, $anzahlstunden, $anzahlminuten;
	global $pdo_handle;
	$buchungsbeginn = $_SESSION['buchungsbeginn'];
	$buchungsende = $_SESSION['buchungsende'];
	$preis = $_SESSION['userpreis'];
	$userid =  $_SESSION['userid'];
	if ($_SESSION['buchung'] == 'nichtausgefuehrt')
		{
		// Konstante wird im SQL-string nicht interpoliert.

		$sql = "INSERT into studio_buchung (userID, begintime, endtime, preis)
			VALUES(:userid, from_unixtime(:buchungsbeginn), from_unixtime(:buchungsende), :preis)";

		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':userid', $userid);
		$stmt -> bindParam(':buchungsbeginn', $buchungsbeginn);
		$stmt -> bindParam(':buchungsende', $buchungsende);
		$stmt -> bindParam(':preis', $preis);
		$ok = $stmt -> execute();
		fehlersuche($ok,'$stmt execute?');
		$_SESSION['buchung'] = 1;
		buchung_mailen($userid,$buchungsbeginn,$buchungsende);

		unset($_SESSION['halbestunde']);
		unset($_SESSION['stunde']);
		unset($_SESSION['tag']);
		unset($_SESSION['monat']);
		unset($_SESSION['jahr']);
		unset($_SESSION['bishalbestunde']);
		unset($_SESSION['bisstunde']);
		unset($_SESSION['bistag']);
		unset($_SESSION['bismonat']);
		unset($_SESSION['bisjahr']);
		unset($_SESSION['buchungsbeginn']);
		unset($_SESSION['buchungsende']);
		unset($_SESSION['wochentag']);
		unset($_SESSION['biswochentag']);
		if ($ok)
			{ ?> 
			<table>
				<tr><td>Buchungsbeginn</td><td><?php print $wochentag;?>, <?php print $anfang;?></td></tr>
				<tr><td>Buchungsende</td><td><?php print $biswochentag;?>, <?php print $ende;?></td></tr>
				<tr><td>Dauer</td><td><?php print $anzahlstunden;?>:<?php print $anzahlminuten;?> Stunden</td></tr>
			</table>
			<p>Die Buchung wurde ausgeführt.</p>
		<?php } else { ?> 
			<p>Es gab einen Fehler beim Eintragen in die Datenbank.</p>
		<?php }
	} ?> 
	<ul>
		<li><a href='<?php print NEUBUCHEN;?>'>Neu buchen</a></li>
		<li><a href='<?php print MEINEBUCHUNGEN;?>'>Meine Buchungen</a></li>
		<li><a href='<?php print BUCHUNGAENDERN;?>'>Buchung ändern</a></li>
	</ul>
<?php }

?>
