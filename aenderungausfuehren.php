<?php
$titel = "Buchungsänderung ausführen";
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
logincheck();
if ($_SESSION['login'] == 1)
	{
	if (!isset($_SESSION['aenderung']))
		{
		$_SESSION['aenderung'] = 'steht_aus';
	}
	$aenderungsid = $_SESSION['aendern'];
	$userid = $_SESSION['userid'];
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
	global $aenderungsid, $userid;
	global $pdo_handle;

	$sql = "SELECT id,
	 	UNIX_TIMESTAMP(begintime) as 'begintime',
	 	UNIX_TIMESTAMP(endtime) as 'endtime'
		FROM studio_buchung
		WHERE id = :aenderungsid
		LIMIT 0,1";

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':aenderungsid', $aenderungsid);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);

	$daten = array();

	for ($i=0;$i<count($result);$i++)
		{
		foreach ($result[$i] as $schluessel => $wert)
			{
			$daten[$schluessel] = $wert;
		}
	}
	fehlersuche($daten,'Daten-Array');

	$_POST['halbestunde'] = date('i', $daten['begintime']);;
	$_POST['stunde'] = date('H', $daten['begintime']);
	$_POST['tag'] = date('d', $daten['begintime']);
	$_POST['monat'] = date('n', $daten['begintime']);
	$_POST['jahr'] = date('Y', $daten['begintime']);
	$_POST['bishalbestunde'] = date('i', $daten['endtime']);;
	$_POST['bisstunde'] = date('H', $daten['endtime']);
	$_POST['bistag'] = date('d', $daten['endtime']);
	$_POST['bismonat'] = date('n', $daten['endtime']);
	$_POST['bisjahr'] = date('Y', $daten['endtime']);
	$timedefaults = array(
		'halbestunde' => '00',
		'stunde' => date('H'),
		'tag' => date('d'),
		'monat' => date('n'),
		'jahr' => date('Y'),
		'bishalbestunde' => '00',
		'bisstunde' => date('H'),
		'bistag' => date('d'),
		'bismonat' => date('n'),
		'bisjahr' => date('Y')
	);
	if ($fehler) {
		print '<ul><li>';
		print implode('</li><li>',$fehler);
		print '</li></ul>';
	}
	print "<form method=\"POST\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";
	print "\t<fieldset>\n";
	print "\t<legend>Änderung ausführen</legend>\n";
	print "<table>";
	print "<tr><td colspan=\"5\">Buchungsbeginn:</td></tr>\n";
	print "<tr><td>";
	input_select('tag', $timedefaults, $tage);
	input_select('monat', $timedefaults, $monate);
	input_select('jahr',  $timedefaults, $jahre);
	input_select('stunde', $timedefaults, $stunden);
	input_select('halbestunde', $timedefaults, $halbestunden);
	print "</td></tr>";
	print "<tr><td colspan=\"5\">Buchungsende:</td></tr>\n";
	print "<tr><td>";
	input_select('bistag', $timedefaults, $tage);
	input_select('bismonat', $timedefaults, $monate);
	input_select('bisjahr',  $timedefaults, $jahre);
	input_select('bisstunde', $timedefaults, $stunden);
	input_select('bishalbestunde', $timedefaults, $halbestunden);
	print "</td></tr>";
	print "\t<tr>\n";
	// $feldname, $colspan, $label
	input_submit('absenden','0', 'Änderung ausführen');
	print "\t</tr>\n\n";
	print "</table>";
	print "\t</fieldset>\n";
	input_hidden();
	print '</form>';
}

function validiere_formular() {
	global $halbestunden, $stunden, $tage, $monate, $jahre;
	global $monatenumerisch;
	global $aenderungsid, $userid;
	global $pdo_handle;

	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	$halbestunde = intval($halbestunden[$_POST['halbestunde']]);
	$stunde = intval($stunden[$_POST['stunde']]);
	$tag = intval($tage[$_POST['tag']]);
	$monat = intval($monatenumerisch[$monate[$_POST['monat']]]);
	$jahr = intval($jahre[$_POST['jahr']]);
	$bishalbestunde = intval($halbestunden[$_POST['bishalbestunde']]);
	$bisstunde = intval($stunden[$_POST['bisstunde']]);
	$bistag = intval($tage[$_POST['bistag']]);
	$bismonat = intval($monatenumerisch[$monate[$_POST['bismonat']]]);
	$bisjahr = intval($jahre[$_POST['bisjahr']]);
	$buchungsbeginn = mktime($stunde,$halbestunde,0,$monat,$tag,$jahr);
	$buchungsende = mktime($bisstunde,$bishalbestunde,0,$bismonat,$bistag,$bisjahr);
	$now = time();
	$dauer = $buchungsende - $buchungsbeginn;
	if ($dauer < 30*60)
		{
		$fehler[] = 'Die Buchung sollte mindestens eine halbe Stunde umfassen.';
	}
	if ($buchungsbeginn < (time() - 1800))
		{
		$fehler[] = 'Der Buchungsbeginn liegt in der Vergangenheit.';
	}
	// große Klammer, um die $aenderungsid rauszufiltern
	$sql = "SELECT	DATE_FORMAT(begintime,'%d.%m.%Y %H:%i:%s') as 'Beginn',
			DATE_FORMAT(endtime,'%d.%m.%Y %H:%i:%s') as 'Ende'
		FROM studio_buchung
		WHERE ((from_unixtime(:buchungsbeginn) >= begintime AND from_unixtime(:buchungsbeginn) < endtime)
			OR (from_unixtime(:buchungsende) > begintime AND from_unixtime(:buchungsende) <= endtime)
			OR (from_unixtime(:buchungsbeginn) <= begintime AND from_unixtime(:buchungsende) >= endtime))
		AND id != :aenderungsid";
	fehlersuche($sql,'SQL');

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':buchungsbeginn', $buchungsbeginn);
	$stmt -> bindParam(':buchungsende', $buchungsende);
	$stmt -> bindParam(':aenderungsid', $aenderungsid);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (count($result) > 0)
		{
		if (count($result) == 1)
			$fehler[] = 'Buchung nicht möglich, es gibt folgende Buchung:';
			else
			$fehler[] = 'Buchung nicht möglich, es gibt folgende Buchungen:';
	 	for ($i=0;$i<count($result);$i++)
			 {
			 foreach ($result[$i] as $schluessel => $wert)
				 {
				$fehler[] = "$schluessel: $wert";
			 }
	 	}
	}
	if (!array_key_exists($_POST['halbestunde'], $halbestunden) || !array_key_exists($_POST['bishalbestunde'], $halbestunden))
		{
		$fehler[] = 'Wähle eine gültige Zeit (Minuten).';
	}
	if (!array_key_exists($_POST['stunde'], $stunden) || !array_key_exists($_POST['bisstunde'], $stunden))
		{
		$fehler[] = 'Wähle eine gültige Zeit (Stunden).';
	}
	if (!array_key_exists($_POST['tag'], $tage) || !array_key_exists($_POST['bistag'], $tage))
		{
		$fehler[] = 'Wähle einen gültigen Tag.';
	}
	if (!array_key_exists($_POST['monat'], $monate) || !array_key_exists($_POST['bismonat'], $monate))
		{
		$fehler[] = 'Wähle einen gültigen Monat.';
	}
	if (!array_key_exists($_POST['jahr'], $jahre) || !array_key_exists($_POST['bisjahr'], $jahre))
		{
		$fehler[] = 'Wähle ein gültiges Jahr.';
	}
	return $fehler;
}

function verarbeite_formular()
	{
	global $halbestunden, $stunden, $tage, $monate, $jahre;
	global $monatenumerisch;
	global $wochentage;
	global $aenderungsid, $userid;
	global $pdo_handle;

	$halbestunde = intval($halbestunden[$_POST['halbestunde']]);
	$stunde = intval($stunden[$_POST['stunde']]);
	$tag = intval($tage[$_POST['tag']]);
	$monat = intval($monatenumerisch[$monate[$_POST['monat']]]);
	$jahr = intval($jahre[$_POST['jahr']]);
	$bishalbestunde = intval($halbestunden[$_POST['bishalbestunde']]);
	$bisstunde = intval($stunden[$_POST['bisstunde']]);
	$bistag = intval($tage[$_POST['bistag']]);
	$bismonat = intval($monatenumerisch[$monate[$_POST['bismonat']]]);
	$bisjahr = intval($jahre[$_POST['bisjahr']]);
	$buchungsbeginn = mktime($stunde,$halbestunde,0,$monat,$tag,$jahr);
	$buchungsende = mktime($bisstunde,$bishalbestunde,0,$bismonat,$bistag,$bisjahr);
	$wochentag = $wochentage[date('w', $buchungsbeginn)];
	$biswochentag = $wochentage[date('w', $buchungsende)];
	if ($_SESSION['aenderung'] != 'vollzogen')
		{
		$sql = "UPDATE studio_buchung
			SET begintime = from_unixtime(:buchungsbeginn),
				endtime = from_unixtime(:buchungsende)
			WHERE id = :aenderungsid";

		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':buchungsbeginn', $buchungsbeginn);
		$stmt -> bindParam(':buchungsende', $buchungsende);
		$stmt -> bindParam(':aenderungsid', $aenderungsid);
		$ok = $stmt -> execute();

		//buchung mailen
		$buchungstext='Buchungsänderung';
		if (!$ok) $buchungstext='Die Änderung dieser Buchung hat nicht funktioniert!';
		buchung_mailen($userid,$buchungsbeginn,$buchungsende,$buchungstext);

		$sql = "SELECT
		 	DATE_FORMAT(begintime, '%d.%m.%Y %H:%i') as 'Beginn',
		 	DATE_FORMAT(endtime, '%d.%m.%Y %H:%i') as 'Ende'
			FROM studio_buchung
			WHERE id = :aenderungsid";

		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':aenderungsid', $aenderungsid);
		$stmt -> execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt -> execute();
		if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));

		//function pdo_result_out($result,$columnkeys,$caption = 'Tabelle')
		if ($ok)
			{
			$text = 'Diese Buchung wurde geändert:';
		} else {
			$text = 'Die Änderung dieser Buchung hat nicht funktioniert (bitte Admin informieren):';
		}
		pdo_result_out($result,$columnkeys,$text);
		print "<p><a href=\"" . MEINEBUCHUNGEN . "\">Meine Buchungen &rarr;</a></p>";
		$_SESSION['aenderung'] = 'vollzogen';
	}
}

fehlersuche ($_POST, 'table');
fehlersuche ($_SESSION, 'table');
require('includes/footer.php');
?>
