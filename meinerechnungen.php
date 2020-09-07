<?php
$titel = "Meine Rechnungen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


$link = connect();
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
	global $pdo_handle;
	print "<h3>Rechnungsübersicht</h3>";
	$user = $_SESSION['benutzer'];
	$userid = $_SESSION['userid'];
	// Abfragen: Buchungen eines Monats, davon die Preise, Anzahl, Summe
	// ausrechnen per PHP für die Monatsrechung
	$bereich = strtotime('-1 month', time());
	$rechnungsjahr = date('Y', $bereich);
	$rechnungsmonate = array(
		'01',
		'02',
		'03',
		'04',
		'05',
		'06',
		'07',
		'08',
		'09',
		'10',
		'11',
		'12'
	);
	foreach($rechnungsmonate as $schluessel => $rechnungsmonat)
		{
		$_SESSION['rechnungsjahr'] = $rechnungsjahr;

		$sql = "select preis as ' &euro;/Std. ',
			DATE_FORMAT(begintime, '%d.%m.%Y %H:%i')as Beginn,
			DATE_FORMAT(endtime, '%d.%m.%Y %H:%i')as Ende,
			TIME_TO_SEC(TIMEDIFF(endtime,begintime)) as sec,
			TIMEDIFF(endtime,begintime) as Dauer,
			TIME_TO_SEC(TIMEDIFF(endtime,begintime))*preis/3600 as Betrag
			from studio_buchung
			where DATE_FORMAT(begintime, '%Y-%m') = :format_begintime
			AND userID = :userid
			order by begintime";


		// pdo_out($pdo_handle,$sql, "Einzelaufstellung $rechnungsmonat/$rechnungsjahr");
		$format_begintime = "$rechnungsjahr-$rechnungsmonat";
		fehlersuche($format_begintime);
		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':userid', $userid);
		$stmt -> bindParam(':format_begintime',$format_begintime);
		$stmt -> execute();
		// $spalten_anzahl = $stmt->columnCount();
		$result = $stmt->fetchAll(PDO::FETCH_OBJ);
		// out($result);
		$stmt -> execute();
		if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
		// out($columnkeys);
		$caption = "Buchungen $rechnungsmonat/$rechnungsjahr";
		$gesamtdauer = 0;
		$summe = 0;
		if ($result)
			{
			// 2 Felder für gesamtdauer und summe
			$colspan = count($columnkeys) - 2;
			print "<table class=\"rahmen\">\n";
			print "<caption>$caption</caption>\n";
			print "\t<tr>\n";
			for ($i = 0; $i < count($columnkeys); $i++)
				{
				print "\t<th>$columnkeys[$i]</th>\n";
			}
			print "\t</tr>\n\n";


			for ($i=0;$i<count($result);$i++)
				{
				print "\t<tr>\n";
				foreach ($result[$i] as $schluessel => $wert)
					{
					print "\t<td>$wert</td>\n";
				}
				$gesamtdauer += $result[$i]->sec;
				$summe += $result[$i]->Betrag;
				print "\t</tr>\n\n";
			}


			print "\t<tr>\n";
			print "\t<td colspan=\"$colspan\">Summe:</td>\n";
			print "\t<td>" . $gesamtdauer/3600 . " Std.</td>\n";
			print "\t<td>" . sprintf('%1.2f', $summe) . " &euro;</td>\n";
			print "\t</tr>\n\n";
			print "</table>\n\n";
			$gesamtdauer = 0;
			$summe = 0;
		} else {
			print "<p>Keine $caption.</p>\n";
		}
	unset ($result);
	}
}
fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');
?>
