<?php
$titel = "Meine Buchungen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php');

global $pdo_handle;

print "<h2>$titel</h2>";
// siehe kopf.php
if ($_SESSION['login'] == 0) print "<p>Bitte logge dich ein!</p>";
if ($_SESSION['login'] == 1)
	{
	print "<h3>Buchungsübersicht</h3>";
	$user = $_SESSION['benutzer'];
	$userid = $_SESSION['userid'];
	fehlersuche($userid, 'Userid');
	$now = time();
	$sql = "select
	 	DATE_FORMAT(begintime, '%d.%m.%Y %H:%i') as 'Beginn',
	 	DATE_FORMAT(endtime, '%d.%m.%Y %H:%i') as 'Ende'
	 	FROM studio_buchung
	 	WHERE userID = :userid
	 	AND endtime >= from_unixtime(:now)
		ORDER BY begintime
	 	LIMIT 0,150";
 	fehlersuche($sql,'SQL');
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':userid', $userid);
	$stmt -> bindParam(':now', $now);
	$stmt -> execute();
	// function pdo_out($dbh,$abfrage,$caption = 'Mysql-Tabelle')
	$caption = "Buchungen von " . htmlspecialchars($user);
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);
	fehlersuche($result,'Ergebnis meine Buchungen');
	$stmt -> execute();
	if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
	fehlersuche($columnkeys,'Columnkeys');
	if ($result)
		{
		pdo_result_out($result,$columnkeys,$caption);
	} else {
		print "<p>Keine $caption.</p>\n";
	}
	unset ($result);
}
fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');
?>
