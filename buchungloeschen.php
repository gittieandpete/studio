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
require('includes/navi.php'); ?> 

<h2><?php print $titel;?></h2>

<?php $idwerte = array();
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
	if ($fehler) { ?> 
		<ul class='meldung'>
			<li><?php print implode("</li>\n\t<li>",$fehler) ?></li>
		</ul>
	<?php }
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
	fehlersuche($result,'Abfrage Löschung DB'); ?> 
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset><legend>Buchung aussuchen</legend>
			<table class='rahmen'>
			<caption>Buchungen</caption>
				<tr>
	<?php for ($i = 0; $i < count($columnkeys); $i++)
		{ ?> 
					<th><?php print $columnkeys[$i];?></th>
	<?php }
	// zusätzliche Spalte Radio-Button ?> 
					<th>Wahl</th>
				</tr>
	<?php for ($i=0;$i<count($result);$i++)
		{ ?> 
				<tr>
		<?php foreach ($result[$i] as $schluessel => $wert)
			{ ?> 
					<td><?php print $wert;?></td>
		<?php }
		// function input_radiocheck($typ, $elementname, $werte, $elementwert) ?> 
					<td><?php input_radiocheck('radio', 'loeschen', $idwerte, $result[$i]['id']); ?></td>
				</tr>
	<?php } ?> 
				<tr>
	<?php // $feldname, $colspan, $label
	input_submit('absenden','4','löschen'); ?> 
				</tr>
			</table>
		</fieldset>
	<?php input_hidden(); ?> 
	</form>
<?php }

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
	pdo_result_out($result,$columnkeys,$caption); ?> 
	<p><a href='<?php print LOESCHUNGAUSFUEHREN;?>'>Weiter &rarr; (Löschung ausführen)</a></p>
<?php }

fehlersuche ($_POST);
fehlersuche ($_SESSION);
require('includes/footer.php');
?>
