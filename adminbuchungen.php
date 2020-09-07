<?php
$titel = "Alle Buchungen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php');

// Konstanten siehe definitions.php
/*
Admin-Werte:
		0 eigene Buchungen anlegen/bearbeiten/löschen
		1 User eintragen (%2>0)
		2 alle Rechnungen sehen (%4>1)
		4 alle Buchungen sehen (>3)
Addition der Berechtigungen ist möglich, z.B. 7 darf alles
*/

if ($_SESSION['login'] == 0) print "<p>Bitte <a href=\"" . LOGIN . "\">logge</a> dich <a href=\"" . LOGIN . "\">ein</a>!</p>";

if ($_SESSION['admin']>3)
	{
	global $pdo_handle;
	print "<h2>$titel</h2>";
	// Buchungen sehen
	$rechnungsjahr = date('Y');
	$sql = " select
		studio_buchung.userID as 'ID',
		studio_user.name as 'Name',
		studio_buchung.begintime as 'Beginn',
		studio_buchung.endtime as 'Ende'
		from studio_buchung, studio_user
		where studio_buchung.userID = studio_user.id
		AND DATE_FORMAT(begintime, '%Y') ='$rechnungsjahr'
		order by begintime";
	// $sql = mysql_query($sql);
	pdo_out($pdo_handle,$sql,'Alle Buchungen dieses Jahr');
}


require('includes/footer.php');
?>
