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
require('includes/navi.php');
print "<h2>$titel</h2>";

logincheck();

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
	if ($fehler = validiere_formular())
		{
		zeige_formular($fehler);
	} else {
		verarbeite_formular();
	}
} else {
	zeige_formular();
}

function zeige_formular($fehler = '')
	{
	global $wochentag, $anfang, $biswochentag, $ende, $anzahlstunden, $anzahlminuten;
	if ($fehler) {
		print '<ul><li>';
		print implode('</li><li>',$fehler);
		print '</li></ul>';
	}
	print "<form method=\"POST\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";
	print "\t<fieldset>\n";
	print "\t<legend>Buchung ausführen</legend>\n";
	print <<<HTML
    <table>
        <tr><td>Buchungsbeginn</td><td>$wochentag, $anfang</td></tr>
        <tr><td>Buchungsende</td><td>$biswochentag, $ende</td></tr>
        <tr><td>Dauer</td><td>$anzahlstunden:$anzahlminuten Stunden</td></tr>
HTML;
	print "\t<tr>\n";
	// $feldname, $colspan, $label
	input_submit('ok','1','ok');
	print "\t</tr>\n\n";
	print "</table>";
	print "\t</fieldset>\n";
	input_hidden();
	print '</form>';
}

function validiere_formular()
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

function verarbeite_formular()
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
		// buchung_mailen($userid,$buchungsbeginn,$buchungsende,$text='Buchung')
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
			{
			print <<<HTML
			<table>
				<tr><td>Buchungsbeginn</td><td>$wochentag, $anfang</td></tr>
				<tr><td>Buchungsende</td><td>$biswochentag, $ende</td></tr>
				<tr><td>Dauer</td><td>$anzahlstunden:$anzahlminuten Stunden</td></tr>
			</table>
HTML;
			print "<p>Die Buchung wurde ausgeführt.</p>";
		} else {
			print "<p>Es gab einen Fehler beim Eintragen in die Datenbank.</p>";
		}
	}
	print "<ul>";
	print "<li><a href=\"" . NEUBUCHEN . "\">Neu buchen</a></li>";
	print "<li><a href=\"" . MEINEBUCHUNGEN . "\">Meine Buchungen</a></li>";
	print "<li><a href=\"" . BUCHUNGAENDERN . "\">Buchung ändern</a></li>";
	print "</ul>";
}

} // logincheck

fehlersuche ($_POST);
fehlersuche ($_SESSION);
require('includes/footer.php');
?>
