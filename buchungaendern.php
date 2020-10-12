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
require('includes/navi.php'); ?>

<h2><?php print $titel;?></h2>

<?php // siehe kopf.php
logincheck();
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
	if ($fehler) { ?>
		<ul class='meldung'>
			<li><?php print implode("</li>\n\t<li>",$fehler);?>
			</li>
		</ul>
	<?php }
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
	if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC)); ?>
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
		<legend>Buchung aussuchen</legend>
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
					<td><?php input_radiocheck('radio', 'aendern', $idwerte, $result[$i]['id']); ?></td>
				</tr>
	<?php } ?>
				<tr>
	<?php // $feldname, $colspan, $label
	input_submit('absenden','4','ändern'); ?>
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
	$_SESSION['aendern'] = $_POST['aendern'];
	// see definitons.php
	header('Location: ' . AENDERUNGAUSFUEHREN);
}

fehlersuche ($_POST, 'Post');
fehlersuche ($_SESSION, 'Session');

require('includes/footer.php');
?>
