<?php
$titel = "Kalender/Übersicht";
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


// Verwende die Hilfsfunktionen für Formulare, die in Kapitel 6 definiert wurden
require 'formularhelfer.php';

$monate = array(1 => 'Januar',
	2 => 'Februar',
	3 => 'Maerz',
	4 => 'April',
	5 => 'Mai',
	6 => 'Juni',
	7 => 'Juli',
	8 => 'August',
	9 => 'September',
	10 => 'Oktober',
	11 => 'November',
	12 => 'Dezember');

$jahre = array();
for ($jahr = date('Y') - 1, $max_jahr = date('Y') + 5; $jahr < $max_jahr; $jahr++) {
	$jahre[$jahr] = $jahr;
}

if (isset($_POST['_abgeschickt_test'])) {
	if ($fehler = validiere_formular(  )) {
		zeige_formular($errors);
	} else {
		zeige_formular(  );
		verarbeite_formular(  );
	}
} else {
	// Wurde das Formular nicht übermittelt, zeige das Formular und dann
	// einen Kalender für den aktuellen Monat an
	zeige_formular(  );
	zeige_kalender(date('n'), date('Y'));
}

function hole_buchungen($monat,$jahr)
	{
	global $pdo_handle;
	global $result;
	// Monat hier einstellig (daher %c in sql)
	$format_begintime = "$jahr-$monat";
	// print $format_begintime;
	// Buchungen sehen
	$sql = "SELECT studio_user.name as 'Name',
			UNIX_TIMESTAMP(studio_buchung.begintime) as 'Beginn',
			UNIX_TIMESTAMP(studio_buchung.endtime) as 'Ende'
		FROM studio_buchung, studio_user
		WHERE studio_buchung.userID = studio_user.id
		AND DATE_FORMAT(begintime, '%Y-%c') = :format_begintime
		ORDER BY begintime";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':format_begintime', $format_begintime);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function validiere_formular(  ) {
	global $monate, $jahre;
	$fehler = array(  );

	if (! array_key_exists($_POST['monat'], $monate)) {
		$errors[  ] = 'Wählen Sie einen gültigen Monat.';
	}

	if (! array_key_exists($_POST['jahr'], $jahre)) {
		$errors[  ] = 'Wählen Sie ein gültiges Jahr.';
	}

	return $fehler;
}

function zeige_formular($fehler = '') {
	global $monate, $jahre, $aktuelles_jahr;

	// Wenn das Formular übermittelt wurde, lese die Standardwerte aus den
	// übermittelten Variablen
	if (isset($_POST['_abgeschickt_test'])) {
		$standardwerte = $_POST;
	} else {
		// Andernfalls setze eigene Standardwerte: aktuellen Monat und Jahr
		$standardwerte = array('jahr' => date('Y'),
		                       'monat' => date('n'));
	}


	if ($fehler) {
		print 'Bitte beheben Sie die folgenden Fehler: <ul><li>';
		print implode('</li><li>',$fehler);
		print '</li></ul>';
	}

	print '<form class="kalender" method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	input_select_sklar('monat', $standardwerte, $monate);
	input_select_sklar('jahr',  $standardwerte, $jahre);
	input_submit_sklar('absenden','Kalender anzeigen');
	print '<input type="hidden" name="_abgeschickt_test" value="1"/>';
	print '</form>';
}

function verarbeite_formular(  ) {
	zeige_kalender($_POST['monat'], $_POST['jahr']);
}

function zeige_kalender($monat, $jahr) {
	global $monate;
	global $result;
	// Monatsnummer hier einstellig php date('n'), sql DATE_FORMAT(zeit, '%Y-%c')
	hole_buchungen($monat, $jahr);
	$wochentage = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');

	// Ermittle den Unix-Zeitstempel für Mitternacht am Monatsersten
	$erster_tag = mktime(0,0,0,$monat, 1, $jahr);
	// Wie viele Tage hat der Monat?
	$tage_im_monat = date('t', $erster_tag);
	// Welcher Wochentag (numerisch) ist der erste Tag des Monats, den benötigen
	// wir, um die erste Tabellenzelle an die richtige Stelle zu setzen
	$tag_verschiebung = date('w', $erster_tag) ;

	// Den Anfang der Tabelle und die Zeile mit den Namen der Wochentage ausgeben

	print "<table class='kalender'>\n";
	print "\t<tr>\n\t<th colspan='7'>" . $monate[$monat] . ' ' . $jahr . "</th>\n\t</tr>\n\n";
	print "\t<tr>\n";
	print "\t<td>";
	print implode("</td>\n\t<td>", $wochentage);
	print "</td>\n\t</tr>\n\n";
	print "\t<tr>\n";

	// Wenn der erste Tag des Monats z.B. Dienstag ist, dann müssen Sie in
	// der ersten Zeile unter "So" und "Mo" leere Zellen einfügen, damit
	// die Tabellenzelle für den ersten Tag unter "Di" steht
	if ($tag_verschiebung > 0) {
		for ($i = 0; $i < $tag_verschiebung; $i++) { print "\t<td>&nbsp;</td>\n"; }
	}

	// Eine Tabellenzelle für jeden Monatstag ausgeben
	for ($tag = 1; $tag <= $tage_im_monat; $tag++ ) {
		print "\t<td><span class='rechts'>" . $tag . '</span><div class="kalenderzelle">';
		// hier Termin rein, wenn da
		for ($i=0;$i<count($result);$i++)
			{
			if($tag==date('d',$result[$i]['Beginn']))
				{
				print date('H:i',$result[$i]['Beginn']) . '-' . date('H:i',$result[$i]['Ende']) . '<br>';
				print '(' . $result[$i]['Name'] . ')<br>';
			}

		}
		print "</div></td>\n";
		$tag_verschiebung++;
		// Wenn diese Zelle die siebte der Zeile war, beende die
		// Tabellenzeile und setzte $tages_verschiebung zurück
		if ($tag_verschiebung == 7) {
		    $tag_verschiebung = 0;
		    print "\t</tr>\n\n";
		    // Wenn noch weitere Tage folgen, beginne
		    // eine neue Tabellenzeile
		    if ($tag < $tage_im_monat) {
		        print "\t<tr>\n";
		    }
		}
	}

	// An diesem Punkt wurde für jeden Tag des Monats eine Tabellenzelle
	// ausgegeben. Wenn der letzte Tag des Monats kein Samstag ist, muss
	// die letzte Zeile der Tabelle bis zum Ende der Zeile mit einigen
	// leeren Zellen aufgefüllt werden
	if ($tag_verschiebung > 0) {
		for ($i = $tag_verschiebung; $i < 7; $i++) {
		    print "\t<td>&nbsp;</td>\n";
		}
		print "\t</tr>\n";
	}
	print "</table>\n\n";
}
?>
