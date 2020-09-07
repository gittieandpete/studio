<?php
$titel = "Buchung löschen";
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
$idwerte = array();
$now = time();
// siehe kopf.php
logincheck();
if ($_SESSION['login'] == 1)
	{
	global $pdo_handle;
	$_SESSION['loeschung'] = 'steht_aus';
	print "<h3>Buchungsübersicht</h3>";
	// Benutzer-ID suchen
	$userid = $_SESSION['userid'];
	// Buchungs-ID-Liste erzeugen:
	// alle Buchungen ab jetzt
	$sql = "SELECT id
		FROM studio_buchung
		WHERE userID = :userid
		AND UNIX_TIMESTAMP(begintime) > :now";

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':userid', $userid);
	$stmt -> bindParam(':now', $now);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	for ($i=0;$i<count($result);$i++)
		{
		foreach ($result[$i] as $schluessel => $wert)
			{
			$idwerte[] = $wert;
		}
	}
	fehlersuche($idwerte,'ID Werte DB');

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
}

function zeige_formular($fehler = '')
	{
	global $idwerte, $userid, $now;
	global $pdo_handle;
	if ($fehler) {
		print '<ul><li>';
		print implode('</li><li>',$fehler);
		print '</li></ul>';
	}
	$sql = "SELECT id,
			DATE_FORMAT(begintime, '%d.%m.%Y %H:%i') as 'Beginn',
 			DATE_FORMAT(endtime, '%d.%m.%Y %H:%i') as 'Ende'
 		FROM studio_buchung
 		WHERE userID = :userid
 		AND begintime >= from_unixtime(:now)
		ORDER BY begintime
 		LIMIT 0,150";

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':userid', $userid);
	$stmt -> bindParam(':now', $now);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt -> execute();
	if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
	fehlersuche($result,'Abfrage Löschung DB');
	print "<form method=\"POST\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";
	print "\t<fieldset>\n";
	print "\t<legend>Buchung aussuchen</legend>\n";
	print "<table class=\"rahmen\">\n";
	print "<caption>Buchungen</caption>\n";
	print "\t<tr>\n";
	for ($i = 0; $i < count($columnkeys); $i++)
		{
		print "\t<th>$columnkeys[$i]</th>\n";
	}
	// zusätzliche Spalte Radio-Button
	print "\t<th>Wahl</th>\n";
	print "\t</tr>\n\n";

	for ($i=0;$i<count($result);$i++)
		{
		print "\t<tr>\n";
		foreach ($result[$i] as $schluessel => $wert)
			{
			print "\t<td>$wert</td>\n";
		}
		// function input_radiocheck($typ, $elementname, $werte, $elementwert)
		print "\t<td>";
		input_radiocheck('radio', 'loeschen', $idwerte, $result[$i]['id']);
		print "</td>\n";
		print "\t</tr>\n\n";
	}


	print "\t<tr>\n";
	// $feldname, $colspan, $label
	input_submit('absenden','4','löschen');
	print "\t</tr>\n\n";
	print "</table>";
	print "\t</fieldset>\n";
	input_hidden();
	print '</form>';

}

function validiere_formular()
	{
	global $idwerte, $userid, $now;
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	fehlersuche($_POST['loeschen'],'POST löschen Wert:');
	if (isset($_POST['loeschen']))
		{
		if (!in_array($_POST['loeschen'], $idwerte))
			{
			$fehler[] = 'Die ausgewählte Buchung existiert nicht.';
		}
	} else {
		$fehler[] = 'Wähle eine Buchung aus!';
	}
	return $fehler;
}

function verarbeite_formular()
	{
	global $pdo_handle;
	$_SESSION['loeschen'] = $_POST['loeschen'];
	$loeschungsid = intval($_SESSION['loeschen']);
	$userid = intval($_SESSION['userid']);
	$sql = "SELECT DATE_FORMAT(begintime, '%d.%m.%Y %H:%i') as 'Beginn',
	 		DATE_FORMAT(endtime, '%d.%m.%Y %H:%i') as 'Ende'
		FROM studio_buchung
		WHERE id = :loeschungsid
		LIMIT 0,1";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':loeschungsid', $loeschungsid);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt -> execute();
	if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
	//function pdo_result_out($result,$columnkeys,$caption = 'Tabelle')
	$caption='Diese Buchung löschen';
	pdo_result_out($result,$columnkeys,$caption);
	print "<p><a href=\"" . LOESCHUNGAUSFUEHREN . "\">Weiter &rarr; (Löschung ausführen)</a></p>";
}

fehlersuche ($_POST);
fehlersuche ($_SESSION);
require('includes/footer.php');
?>
