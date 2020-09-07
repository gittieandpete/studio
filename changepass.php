<?php
$titel = "Passwort ändern";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php');
print "<h2>$titel</h2>";

if ($_SESSION['login'] == 0) print "<p>Bitte logge dich ein!</p>";
if ($_SESSION['login'] == 1)
	{

	// zugrunde liegende Logik für das Formular
	if (iswech())
		{
		if ($formularfehler = validiere_formular())
			{
			zeige_formular($formularfehler);
		} else {
			verarbeite_formular();
		}
	} else
		{
		zeige_formular();
	}

}
// $fehler = '' ist default und wird z.B. durch zeige_formular($formularfehler) überschrieben
// mit dem Inhalt von $formularfehler
function zeige_formular($fehler = '')
	{
	print "<form method=\"POST\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";
	print "<fieldset>\n";
	print "\t<legend>Passwort ändern</legend>\n";
	if ($fehler) {
		print "<ul class=\"meldung\">\n";
		print "\t<li>" . implode("</li>\n\t<li>",$fehler) . "</li>\n";
		print "</ul>\n\n";
	}
	print "<table>\n";
	// Benutzername
	input_text('benutzer', 'Mailadresse');
	// altes Passwort
	input_passwort('pass_old', 'altes Passwort');
	// Passwort
	input_passwort('passwort', 'neues Passwort');
	input_passwort('passwort_control', 'neues Passwort');
	// Submit $feldname, $colspan, $label kein td
	print "\t<tr>";
	input_submit('absenden','1','Passwort ändern');
	print "\t</tr>\n\n";
	print "</table>\n\n";
	input_hidden();
	print "</fieldset>\n";
	print "</form>\n\n";
}

/*
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':loeschungsid', $loeschungsid);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt -> execute();
	if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
	//function pdo_result_out($result,$columnkeys,$caption = 'Tabelle')
	$caption='Diese Buchung löschen';
	pdo_result_out($result,$columnkeys,$caption);
*/

function validiere_formular() {
	global $pdo_handle;
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	// Positiv-Liste
	$sql = "SELECT user, pass
		FROM studio_user";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	fehlersuche($result,'User-Pass');

	if ($result)
		{
		for($i=0;$i<count($result);$i++)
			{
			$benutzer = $result[$i]['user'];
			$pass = $result[$i]['pass'];
			$user[$benutzer] = $pass;
		}
	}
	fehlersuche($user,'Array User Pass');
	// Sicherstellen, dass der Benutzername gültig ist
	$fehlermeldung = 'Bitte gib einen gültigen Benutzernamen und ein gültiges Passwort ein.';
	if (! array_key_exists($_POST['benutzer'], $user))
		{
		$fehler[0] = $fehlermeldung;
	} elseif ($user[$_POST['benutzer']] != md5($_POST['pass_old']))
		// Prüfen, ob das Passwort korrekt ist
		// gespeichertes Passwort = $user[$_POST['benutzer']];
		{
		// Fehlermeldung gleich+wird ggf. überschrieben, erlaubt keine Rückschlüsse
		$fehler[0] = $fehlermeldung;
	}
	if ($_POST['passwort'] != $_POST['passwort_control'])
		{
		$fehler[] = "Bitte gib zwei mal dasselbe neue Passwort ein!";
	}
	return $fehler;
}

function verarbeite_formular()
	{
	global $pdo_handle;
	$user = $_SESSION['benutzer'];
	$dbpass = md5($_POST['passwort']);
	$sql = "UPDATE studio_user
		SET pass = :dbpass, pass_changed = '1'
		WHERE user = :user
		LIMIT 1";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':dbpass', $dbpass);
	$stmt -> bindParam(':user', $user);
	$ok = $stmt -> execute();

	if ($ok)
		{
		print "<p>Hallo " . $_SESSION['vorname'] . ", <br><strong>das Passwort</strong> für " . $user . " <strong>wurde erfolgreich geändert</strong>!</p>\n\n";
	} else {
		print "Ein Datenbankfehler ist passiert. Bitte versuch es noch mal oder wende dich per Mail an peter.mueller@c-major.de!";
		zeige_formular();
	}
}

fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');
?>
