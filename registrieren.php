<?php
$titel = "Registrieren";
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

// ist momentan deaktiviert

print "<p>...ist hier nicht möglich.</p>";

/*

// Tabellenname
$tabname = "studio_user";
// domain studio_registrierung
// studio_reg_

// zugrunde liegende Logik für das Formular
if (iswech())
	{
	if ($formularfehler = studio_reg_validiere_formular())
		{
		studio_reg_zeige_formular($formularfehler);
	} else {
		studio_reg_verarbeite_formular();
	}
} elseif ($_SESSION['login'] == 0)
	{
	studio_reg_zeige_formular();
} else {
	print "<p>Du bist bereits registriert.</p>";
}

// $fehler = '' ist default und wird z.B. durch zeige_formular($formularfehler) überschrieben
// mit dem Inhalt von $formularfehler
function studio_reg_zeige_formular($fehler = '')
	{
	print "<form method=\"POST\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";
	print "<fieldset>\n";
	print "\t<legend>Registrieren</legend>\n";
	if ($fehler) {
		print "<ul class=\"meldung\">\n";
		print "\t<li>" . implode("</li>\n\t<li>",$fehler) . "</li>\n";
		print "</ul>\n\n";
	}
	print "<table>\n";
	// Benutzername
	if (iswech())
		{
		input_text_post('mail', $_POST, 'Mailadresse');
		input_text_post('vorname', $_POST, 'Vorname');
		input_text_post('name', $_POST, 'Name');
	} else {
		input_text('mail', 'Mailadresse');
		input_text('vorname', 'Vorname');
		input_text('name', 'Name');
	}


	// Submit
	input_submit('absenden','Registrieren');

	print "</table>\n\n";
	print "<input type=\"hidden\" name=\"abgeschickt\" value=\"1\">\n";
	print "</fieldset>\n";
	print "</form>\n\n";
}

function studio_reg_validiere_formular()
	{
	$fehler = array();
	$postuser = mysql_real_escape_string($_POST['mail']);
	$abfrage = "select distinct user from tippspiel_user where user = '$postuser'";
	$abfrage = mysql_query($abfrage);
	while ($liste = mysql_fetch_array($abfrage, MYSQL_ASSOC))
		{
		foreach ($liste as $inhalt)
			{
			$dbuser = $liste['user'];
		}
	}
	if (isset($dbuser) && $postuser == $dbuser)
		{
		$fehler[] = "Diese Mailadresse ist bereits registriert. Bitte gehe zur <a href=\"" . LOGIN . "\">Login-Seite</a>!";
		return $fehler;
	}
	if (strlen($_POST['mail']) > 250)
		{
		$fehler[] = 'Die Mail-Adresse ist zu lang';
	}
	if (strlen($_POST['vorname']) > 250)
		{
		$fehler[] = 'Der Vorname ist zu lang';
	}
	if (strlen($_POST['name']) > 250)
		{
		$fehler[] = 'Der Name ist zu lang';
	}
	$text = $_POST['mail'];
	// simples selbstgestricktes Muster
	$muster = '/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/';
	$at = preg_match($muster, $text);
	if (!$at)
		{
		$fehler[] = 'Bitte gib eine gültige Mailadresse ein';
	}
	return $fehler;
}


function studio_reg_verarbeite_formular()
	{
	// für die Datenbank
	$mail = mysql_real_escape_string($_POST['mail']);
	$vorname = mysql_real_escape_string($_POST['vorname']);
	$name = mysql_real_escape_string($_POST['name']);

	// Die Daten in die Tabelle einfügen
	$tabname = $GLOBALS['tabname'];

	$kons = array (
		'B',
		'D',
		'F',
		'G',
		'K',
		'L',
		'M',
		'N',
		'P',
		'R',
		'S',
		'T',
		'V',
		'W',
		'Z');
	$voc = array (
		'E',
		'I',
		'O',
		'U');
	$k1 = rand(1,count($kons)) - 1;
	$k2 = rand(1,count($kons)) - 1;
	$k3 = rand(1,count($kons)) - 1;
	$v1 = rand(1,count($voc)) - 1;
	$v2 = rand(1,count($voc)) - 1;
	$v3 = rand(1,count($voc)) - 1;

	$pass = $kons[$k1] . $voc[$v1] . $kons[$k2] . $voc[$v2] . $kons[$k3] . $voc[$v3];
	// Mail senden
	$strEmpfaenger =& $mail;

	# Welchen Betreff soll die Mail erhalten?
	$strSubject  = 'Login';

	# Mail-Layout
	$kopf = "Hallo $vorname $name,\n";
	$inhalt = "Vielen Dank für die Registrierung. Dein vorläufiges Passwort ist \n$pass. Bitte gehe auf unsere Seite " . PASSWORTAENDERN . " und ändere beim ersten Mal das Passwort!\n\nMailadresse:\t$mail\nPasswort:\t$pass\n\n";
	$fuss = "Viele Grüße,\n\nPeter\n";


	$mailtext = $kopf . $inhalt . $fuss;

	$reply = 'peter.mueller@c-major.de';
	$header = "From: " . $mail . "\nReply-To: " . $reply;

	print "<p>Es wird versucht, eine Mail an die angegebene Adresse zu schicken. Bitte schau in dein Postfach!</p>";

	// abschicken
	// print "An: $strEmpfaenger<br>\nBetreff: $strSubject<br>\nText: $mailtext<br>\nHeader: $header<br>";
	mail($strEmpfaenger, $strSubject, $mailtext, $header) or die("<p>Die Mail konnte nicht versendet werden. </p>");



	$dbpass = crypt($pass,SALT);
	// pass_changed ist default 0;
	$insert = "insert into $tabname (user, vorname, name, pass) values ('$mail', '$vorname', '$name', '$dbpass')";
	mysql_query($insert);

}

*/

require('includes/footer.php');
?>
