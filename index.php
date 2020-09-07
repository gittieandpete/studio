<?php
$titel = "Login";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');

print "<h2>$titel</h2>";
// gesetzte Variablen (session+post) siehe kopf.php
// siehe functions.php
if (!isset($_SESSION['sleep']))
	{
	$_SESSION['sleep']=0.5;
	fehlersuche($_SESSION['sleep']);
}

if (iswech() && $_SESSION['login'] == 0)
	{
	if ($formularfehler = validiere_loginformular())
		{
		fehlersuche('<p>1. zeige login mit Fehlern</p>');
		zeige_loginformular($formularfehler);
	} else {
		fehlersuche('<p>2. else verarbeite login</p>');
		verarbeite_loginformular();
	}
} elseif ($_SESSION['login'] == 0 )
	{
	fehlersuche('<p>3. elseif zeige login</p>');
	zeige_loginformular();
	print "<ul>";
	print "<li><a href=\"" . PASSWORTVERGESSEN . "\">Passwort vergessen?</a></li>";
	print "</ul>";
}


if ($_SESSION['login'] == 1)
	{
	require('includes/navi.php');
	print "<p>Hallo " . $_SESSION['vorname']. "!</p>\n\n";
}

// $fehler = '' ist default und wird z.B. durch zeige_loginformular($formularfehler) überschrieben mit dem Inhalt von $formularfehler
function zeige_loginformular($fehler = '')
	{
	print "<form method=\"POST\" accept-charset=\"ISO-8859-1\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";
	print "<fieldset>\n";
	print "\t<legend>Login</legend>\n";
	if ($fehler) {
		print "<ul class=\"meldung\">\n";
		print "\t<li>" . implode("</li>\n\t<li>",$fehler) . "</li>\n";
		print "</ul>\n\n";
	}
	print "<table>\n";
	// Benutzername
	input_text('benutzer', 'Mailadresse');
	// Passwort
	input_passwort('passwort', 'Passwort');
	// Submit
	print "\t<tr>\n";
	input_submit('absenden','2','Login');
	print "\t</tr>\n\n";
	print "</table>\n\n";
	// Defaultwert für den Feldnamen ist 'abgeschickt';
	input_hidden();
	print "</fieldset>\n";
	print "</form>\n\n";
}

function validiere_loginformular()
	{
	global $pdo_handle;
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	// Positiv-Liste
	$sql="SELECT user, pass, pass_changed
		FROM studio_user";

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(isset($result))
		{
		for ($i=0;$i<count($result);$i++)
			{
			$benutzer=$result[$i]->user;
			$pass=$result[$i]->pass;
			$pass_changed=$result[$i]->pass_changed;
			// Array herstellen, wie ich es unten brauche
			// z.B. $user_pw_liste['mailadresse'] => 'passwort'
			$user_pw_liste[$benutzer]=$pass;
			$p_C[$benutzer]=$pass_changed;
		}
	}

	fehlersuche($benutzer, 'validiere Login, User');

	fehlersuche($pass_changed, 'validiere Login, Pass changed');
	$fehlermeldung = 'Bitte gib einen gültigen Benutzernamen und ein gültiges Passwort ein. <a href="' . PASSWORTVERGESSEN . '">Passwort vergessen?</a>';
	if (isset($_POST['benutzer']) && isset($user_pw_liste))
		{
		// Sicherstellen, dass der Benutzername gültig ist
		// Die Leute geben ihre Mailadresse auch manchmal mit Großbuchstaben ein.
		$user = strtolower($_POST['benutzer']);
		if (! array_key_exists($user, $user_pw_liste))
			{
			$fehler[1] = $fehlermeldung;
		} elseif ($user_pw_liste[$user] != md5($_POST['passwort']))
			// $user wegen strtolower (nicht $_POST['benutzer'])
			// Prüfen, ob das Passwort korrekt ist
			// gespeichertes Passwort = $user_pw_liste[$_POST['benutzer']];
			{
			// Fehlermeldung gleich+wird ggf. überschrieben, erlaubt keine Rückschlüsse
			$fehler[1] = $fehlermeldung;
		}
	} else	{
		$fehler[1] = $fehlermeldung . ' ERROR in validiere_loginformular: else+debug';
	}
	$wait=$_SESSION['sleep']=$_SESSION['sleep']+0.1;
	sleep(intval($wait));
	fehlersuche($wait);
	return $fehler;
}

function verarbeite_loginformular()
	{
	// Muss das Passwort geändert werden?
	global $pdo_handle;

	$user = strtolower($_POST['benutzer']);
	fehlersuche($user,'verarbeite formular');
	$sql = "SELECT id, vorname, name, userpreis, pass_changed, admin
		FROM studio_user
		WHERE user = :user";

	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> bindParam(':user', $user);
	$stmt -> execute();
	$result = $stmt->fetchAll();

	if(isset($result))
		{
		$userid = $result[0]->id;
		$vorname = $result[0]->vorname;
		$name = $result[0]->name;
		$userpreis = $result[0]->userpreis;
		$p_c = $result[0]->pass_changed;
		$admin = $result[0]->admin;
	}
	fehlersuche($p_c,'NULL 2014');
	if ($p_c == 0)
		{
		print "<p>Das Passwort sollte <a href=\"" . PASSWORTAENDERN . "\">geändert</a> werden.</p>";
	}
	// Der Session den Benutzernamen hinzufügen
	// wichtig: beide Arrays müssen denselben key haben; also, wenn 'benutzer' bei $_SESSION, dann auch 'benutzer' bei $_POST. Brauche ich für die Rückgabe von bereits eingegebenen Werten in Formularen.
	$_SESSION['benutzer'] = $_POST['benutzer'];
	// benutzer eingeloggt
	$_SESSION['login'] = 1;
	$_SESSION['userid'] = $userid;
	$_SESSION['vorname'] = $vorname;
	$_SESSION['name'] = $name;
	$_SESSION['userpreis'] = $userpreis;
	$_SESSION['admin'] = $admin;
	// die Anti-Hack-Zeitschaltuhr abstellen
	$_SESSION['sleep'] = 0.5;
	// print "<p>login verarbeitet " . $_SESSION['login'] . " (session-login)</p>";
	// print "<p>Der Login war erfolgreich! Hallo " . htmlentities($vorname) . ' ' . htmlentities($name) . " (Mail: " . htmlentities($user) . "). </p>\n\n";
}

fehlersuche ($_POST);
fehlersuche ($_SESSION);
require('includes/footer.php');
?>
