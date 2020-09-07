<?php
$titel = "Löschung ausführen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php');
require('includes/datumsangaben.php');
print "<h2>$titel</h2>";
if ($_SESSION['login'] == 0) print "<p>Bitte logge dich ein!</p>";
if ($_SESSION['login'] == 1)
	{
	$loeschungsid = $_SESSION['loeschen'];
	$userid = $_SESSION['userid'];
	$now = time();
	if (!isset($_SESSION['loeschung']))
		{
		$_SESSION['loeschung'] = 'steht_aus';
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

}

function zeige_formular($fehler = '')
	{
	global $halbestunden, $stunden, $tage, $monate, $jahre;
	global $monatenumerisch;
	global $loeschungsid, $userid;
	global $pdo_handle;
	$sql = "SELECT
	 	DATE_FORMAT(begintime, '%d.%m.%Y %H:%i') as 'Beginn',
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

	if ($fehler) {
		print '<ul><li>';
		print implode('</li><li>',$fehler);
		print '</li></ul>';
	}
	print "<form method=\"POST\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";
	print "\t<fieldset>\n";
	print "\t<legend>Löschung anzeigen</legend>\n";
	$caption='Diese Buchung löschen';
	pdo_result_out($result,$columnkeys,$caption);
	print "<table>\n\n";
	print "\t<tr>\n";
	// $feldname, $colspan, $label
	input_submit('absenden','0', 'Löschen');
	print "\t</tr>\n\n";
	print "</table>";
	print "\t</fieldset>\n";
	input_hidden();
	print '</form>';
}

function validiere_formular()
	{
	global $loeschungsid, $userid, $now;
	global $pdo_handle;
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	$dbwerte = array();
	$sql = "SELECT id
		FROM studio_buchung
		WHERE userid = :userid
 		AND begintime >= from_unixtime(:now)";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':userid', $userid);
	$stmt -> bindParam(':now', $now);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	for ($i=0;$i<count($result);$i++)
		{
		foreach ($result[$i] as $schluessel => $wert)
			{
			$dbwerte[] = $wert;
		}
	}
	fehlersuche($dbwerte,'DB Werte Löschung');

	if (!in_array($loeschungsid,$dbwerte))
		{
		$fehler[] = 'Die ausgewählte Buchung existiert nicht.';
	}
	return $fehler;
}

function verarbeite_formular()
	{
	global $loeschungsid;
	global $pdo_handle;
	$loeschungsid = intval($loeschungsid);
	fehlersuche($loeschungsid,'ID Löschung');
	if ($_SESSION['loeschung'] != 'vollzogen')
		{

		// Angaben für buchung_mailen
		$buchungstext='Löschung';
		$sql = "SELECT userID,
				UNIX_TIMESTAMP(begintime) as 'buchungsbeginn',
				UNIX_TIMESTAMP(endtime) as 'buchungsende'
			FROM studio_buchung
			WHERE id = :loeschungsid";
		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':loeschungsid', $loeschungsid);
		$stmt -> execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		fehlersuche($result,'Angaben Mail');
		if ($result)
			{
			// kann nur eine ausgewählt sein.
			$userid = $result[0]['userID'];
			$buchungsbeginn = $result[0]['buchungsbeginn'];
			$buchungsende = $result[0]['buchungsende'];
		}
		buchung_mailen($userid,$buchungsbeginn,$buchungsende,$buchungstext);
	        $sql = "DELETE FROM studio_buchung
			WHERE id = :loeschungsid";
		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':loeschungsid', $loeschungsid);
		$ok = $stmt -> execute();
		if ($ok)
			{
			$_SESSION['loeschung'] = 'vollzogen';
			print "<p>Die Buchung wurde gelöscht.</p>";
			print "<p><a href=\"" . MEINEBUCHUNGEN . "\">Meine Buchungen &rarr;</a></p>";
		} else {
			print "<p>Die Löschung hat nicht funktioniert.</p>";
		}
	}
}

fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');
?>
