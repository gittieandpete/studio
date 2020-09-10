<?php
$titel = "Neu buchen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php');
require('includes/datumsangaben.php'); ?> 

<h2><?php print $titel;?></h2>

<?php logincheck();

if ($_SESSION['login'] == 1)
	{
	if (iswech())
		{
		if ($fehler = validiere_neubuchenformular())
			{
			zeige_neubuchenformular($fehler);
		} else {
			verarbeite_neubuchenformular();
		}
	} else { ?> 
		<p>Hallo <?php print $_SESSION['vorname'];?>!</p>
		<?php password_changed_check();
		zeige_neubuchenformular();
	}
}

fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');

// begin functions

function zeige_neubuchenformular($fehler = '')
	{
	global $halbestunden, $stunden, $tage, $monate, $jahre;
	global $monatenumerisch;
	// Wenn das Formular übermittelt wurde, lese die Standardwerte aus den
	// übermittelten Variablen
	// Andernfalls setze eigene Standardwerte: aktuelle Zeit
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
	if ($fehler) { ?> 
		<ul class='meldung'>
			<li><?php print implode("</li>\t\n<li>",$fehler);?></li>
		</ul>
	<?php } ?> 
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
		<legend>Neu buchen</legend>
			<table>
				<tr>
					<td colspan='5'>Buchungsbeginn:</td>
				</tr>
	
				<tr>
					<td>
	<?php input_select('tag', $timedefaults, $tage);
	input_select('monat', $timedefaults, $monate);
	input_select('jahr',  $timedefaults, $jahre);
	input_select('stunde', $timedefaults, $stunden);
	input_select('halbestunde', $timedefaults, $halbestunden); ?> 
					</td>
				</tr>

				<tr>
					<td colspan='5'>Buchungsende:</td>
				</tr>
		
				<tr>
					<td>
	<?php input_select('bistag', $timedefaults, $tage);
	input_select('bismonat', $timedefaults, $monate);
	input_select('bisjahr',  $timedefaults, $jahre);
	input_select('bisstunde', $timedefaults, $stunden);
	input_select('bishalbestunde', $timedefaults, $halbestunden); ?> 
					</td>
				</tr>

				<tr>
	<?php input_submit('absenden','0','übernehmen'); ?> 
				</tr>
			</table>
		</fieldset>
	<?php input_hidden(); ?> 
	</form>
<?php }

function validiere_neubuchenformular() {
	global $halbestunden, $stunden, $tage, $monate, $jahre;
	global $monatenumerisch;
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
/* in Klartext
where buchungsbeginn >= dbzeitbeginn AND buchungsbeginn < dbzeitende
or buchungsende > dbzeitbeginn AND buchungsende <= dbzeitende
or buchungsbeginn <= dbzeitbeginn AND buchungsende >= dbzeitende
*/
	$sql = "
		select DATE_FORMAT(begintime,'%d.%m.%Y %H:%i:%s') as 'Beginn', DATE_FORMAT(endtime,'%d.%m.%Y %H:%i:%s') as 'Ende'
		from studio_buchung
		where (from_unixtime(:buchungsbeginn) >= begintime AND from_unixtime(:buchungsbeginn) < endtime)
		or (from_unixtime(:buchungsende) > begintime AND from_unixtime(:buchungsende) <= endtime)
		or (from_unixtime(:buchungsbeginn) <= begintime AND from_unixtime(:buchungsende) >= endtime)";
		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':buchungsbeginn', $buchungsbeginn);
		$stmt -> bindParam(':buchungsende', $buchungsende);
		$stmt -> execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		// pdo_out($pdo_handle,$sql);
	if (count($result) > 0)
		{
		if (count($result) == 1)
			{
			$fehler[] = 'Buchung nicht möglich, es gibt folgende Buchung:';
		} else {
			$fehler[] = 'Buchung nicht möglich, es gibt folgende Buchungen:';
		}
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

function verarbeite_neubuchenformular()
	{
	global $halbestunden, $stunden, $tage, $monate, $jahre;
	global $monatenumerisch;
	global $wochentage;
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
	$buchung = 'nichtausgefuehrt';
	$_SESSION['halbestunde'] = $halbestunde;
	$_SESSION['stunde'] = $stunde;
	$_SESSION['tag'] = $tag;
	$_SESSION['monat'] = $monat;
	$_SESSION['jahr'] = $jahr;
	$_SESSION['bishalbestunde'] = $bishalbestunde;
	$_SESSION['bisstunde'] = $bisstunde;
	$_SESSION['bistag'] = $bistag;
	$_SESSION['bismonat'] = $bismonat;
	$_SESSION['bisjahr'] = $bisjahr;
	$_SESSION['buchungsbeginn'] = $buchungsbeginn;
	$_SESSION['buchungsende'] = $buchungsende;
	$_SESSION['wochentag'] = $wochentag;
	$_SESSION['biswochentag'] = $biswochentag;
	$_SESSION['buchung'] = $buchung; ?> 
	<p><a href='<?php print BUCHUNGZEIGEN;?>'>Weiter &rarr; (Buchung zeigen)</a></p>
<?php }

?>
