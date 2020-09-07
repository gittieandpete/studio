<?php
$titel = "Buchung ändern";
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

// siehe kopf.php
if ($_SESSION['login'] == 0) print "<p>Bitte logge dich ein!</p>";
if ($_SESSION['login'] == 1)
	{
	global 	$pdo_handle,
		$idwerte,
		$userid;
	$now = time();
	$_SESSION['aenderung'] = 'steht_aus';
	$userid = $_SESSION['userid'];

	print "<h3>Buchungsübersicht</h3>";
	// Buchungs-ID-Liste erzeugen (keine Änderung für Buchungen, die in der Vergangenheit liegen):
	$sql = "SELECT id
		FROM studio_buchung
		WHERE userID = :userid
		AND UNIX_TIMESTAMP(begintime) > :now
	";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':userid', $userid);
	$stmt -> bindParam(':now', $now);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);

	if ($result)
		{
		for($i=0;$i<count($result);$i++)
			{
			$idwerte[] = $result[$i]->id;
		}

	}

	fehlersuche($idwerte,'ID-Werte von User 2');

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
 		LIMIT 0,150
 	";
 	fehlersuche ($sql,'sql');

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':userid', $userid);
	$stmt -> bindParam(':now', $now);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt -> execute();
	if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));

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
		input_radiocheck('radio', 'aendern', $idwerte, $result[$i]['id']);
		print "</td>\n";
		print "\t</tr>\n\n";
	}


	print "\t<tr>\n";
	// $feldname, $colspan, $label
	input_submit('absenden','3','ändern');
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
	if (isset($_POST['aendern']))
		{
		if (!in_array($_POST['aendern'], $idwerte))
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
	global 	$pdo_handle;
	$_SESSION['aendern'] = $_POST['aendern'];
	$aenderungsid = intval($_SESSION['aendern']);
	$userid = intval($_SESSION['userid']);
	$sql = "SELECT
	 	DATE_FORMAT(begintime, '%d.%m.%Y %H:%i') as 'Beginn',
	 	DATE_FORMAT(endtime, '%d.%m.%Y %H:%i') as 'Ende'
		FROM studio_buchung
		WHERE id = :aenderungsid
		LIMIT 0,1";

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':aenderungsid', $aenderungsid);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt -> execute();
	if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
	$caption = 'Diese Buchung wurde ausgewählt:';
	//function pdo_result_out($result,$columnkeys,$caption = 'Tabelle')
	pdo_result_out($result,$columnkeys,$caption);

	print "<p><a href=\"" . AENDERUNGAUSFUEHREN . "\">Weiter &rarr; (Änderung ausführen)</a></p>";
}
fehlersuche ($_POST, 'Post');
fehlersuche ($_SESSION, 'Session');

require('includes/footer.php');
?>
