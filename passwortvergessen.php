<?php
$titel = "Passwort vergessen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
// require('includes/navi.php'); ?> 

<h2><?php print $titel;?></h2>

<?php if (!isset($_SESSION['passwort']) || !isset($_SESSION['sleep']))
	{
	$_SESSION['passwort'] = 'vergessen';
	$_SESSION['sleep']=0.5;
	fehlersuche($_SESSION['sleep']);
}

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

fehlersuche ($_POST,'POST');
fehlersuche ($_SESSION,'Session');

require('includes/footer.php');

// begin functions

// $fehler = '' ist default und wird z.B. durch zeige_formular($formularfehler) überschrieben
// mit dem Inhalt von $formularfehler
function zeige_formular($fehler = '')
	{ ?> 
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
			<legend>Passwort vergessen</legend>
	<?php if ($fehler) { ?> 
		<ul class='meldung'>
			<li>'<?php print implode("</li>\n\t<li>",$fehler);?>'</li>
		</ul>
	<?php } ?> 
	<table>
	<?php // Benutzername
	input_text('user', 'Mailadresse'); ?> 
		<tr>
	<?php // Submit
	input_submit('absenden', '2', 'Passwort anfordern'); ?> 
		</tr>
	</table>
	<?php input_hidden(); ?> 
	</fieldset>
	</form>
<?php }

function validiere_formular()
	{
	global $pdo_handle;
	$fehler = array();
	// prüft $_POST-Werte auf Länge und Null-String
	$fehler = validiere_post($_POST,$fehler);

	$sql = "SELECT DISTINCT user
		FROM studio_user
		WHERE user = :user
		LIMIT 0,1";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':user', $_POST['user']);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	fehlersuche($result);

	if ($result)
		{
		$_SESSION['user'] = $_POST['user'];
	} else {
		$fehler[] = "Diese Mailadresse (" . htmlspecialchars($_POST['user']) . ") ... ist noch nicht registriert. Bitte den Admin fragen!";
	}
	$wait=$_SESSION['sleep']=$_SESSION['sleep']+0.1;
	sleep(intval($wait));
	fehlersuche($wait,'wait and sleep...');
	return $fehler;
}

function verarbeite_formular()
	{
	global $pdo_handle;
	$user = $_SESSION['user'];
	if ($_SESSION['passwort'] != 'gemailt')
		{
		// Die Daten in die Tabelle einfügen
		// 216000 Möglichkeiten
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
		$strEmpfaenger = $user;
		# Welchen Betreff soll die Mail erhalten?
		$strSubject  = 'Neues Passwort';
		# Mail-Layout
		$kopf = "Hallo,\n";
		$inhalt = "Dein vorläufiges Passwort ist \n" . $pass . ". Bitte ändere nach dem nächsten Login das Passwort!\n\nMailadresse: " . $user . "\nPasswort: " . $pass . "\n\n";
		$fuss = "Viele Grüße,\n\nPeter\n";
		$mailtext = $kopf . $inhalt . $fuss;
		// charset beachten
		$header = "From: peter.mueller@c-major.de\r\nContent-type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
		// abschicken
		fehlersuche("An: $strEmpfaenger<br>
Betreff: $strSubject<br>
Text: $mailtext<br>",'Mailtext');
		// vorher und zum Testen noch Mail an mich
		mail('peter.mueller@c-major.de', 'Passwort vergessen', $mailtext, $header);
		$sent = mail($strEmpfaenger, $strSubject, $mailtext, $header); 
		// Zum Testen, sonst auskommentieren!
		// $sent = 1;
		if (!$sent)	{ ?> 
		<p>Die Mail konnte nicht versendet werden. Das neue Passwort wurde noch nicht erzeugt. Bitte probiere es nochmals oder wende dich per Mail an <a href="mailto:peter.mueller@c-major.de">peter.mueller@c-major.de</a>!</p></p>
		<?php } else {
		$dbpass = password_hash($pass, PASSWORD_DEFAULT);
		// alt, nicht mehr benutzen:
		// $dbpass = md5($pass);
		$sql = "UPDATE studio_user
			SET pass = :dbpass, pass_changed = '0'
			WHERE user = :user
			LIMIT 1";
		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':dbpass', $dbpass);
		$stmt -> bindParam(':user', $user);
		$ok = $stmt -> execute();
		}
		if ($ok)
			{ ?> 
			<p class='meldung'>Ein neues Passwort ist generiert worden und per Mail unterwegs die angegebene Adresse.</p>
			Zurück zur <a href='<?php print $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];?>'>Startseite</a>.			
			<?php $_SESSION['passwort'] = 'gemailt';
		} else { ?> 
			<p class='meldung'>Ein Fehler ist passiert. Bitte probiere es nochmals oder wende dich per Mail an <a href="mailto:peter.mueller@c-major.de">peter.mueller@c-major.de</a>!</p>
			<?php zeige_formular();
			$_SESSION['passwort'] = 'nicht_gemailt';
		}
	}
}

?>
