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
require('includes/datumsangaben.php'); ?> 

<h2><?php print $titel;?></h2>


<?php logincheck();

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
		if ($fehler = validiere_loeschformular())
			{
			zeige_loeschformular($fehler);
		} else {
			verarbeite_loeschformular();
		}
	} else {
		zeige_loeschformular();
	}

}

fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');

// begin functions

function zeige_loeschformular($fehler = '')
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

	if ($fehler) { ?> 
		<ul class='meldung'>
			<li><?php print implode("</li>\n\t<li>",$fehler);?></li>
		</ul>
	<?php } ?> 
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
		<legend>Löschung anzeigen</legend>
	<?php $caption='Diese Buchung löschen';
	pdo_result_out($result,$columnkeys,$caption); ?> 
	<table>
		<tr>
	<?php // $feldname, $colspan, $label
	input_submit('absenden','0', 'Löschen'); ?> 
		</tr>
	</table>
	</fieldset>
	<?php input_hidden(); ?> 
	</form>
<?php }

function validiere_loeschformular()
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

function verarbeite_loeschformular()
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
			$_SESSION['loeschung'] = 'vollzogen'; ?> 
			<p>Die Buchung wurde gelöscht.</p>
			<p><a href='<?php print MEINEBUCHUNGEN;?>'>Meine Buchungen &rarr;</a></p>
		<?php } else { ?> 
			<p>Die Löschung hat nicht funktioniert.</p>
		<?php }
	}
}

?>
