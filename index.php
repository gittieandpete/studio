<?php
$titel = "Login";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php'); ?> 

<h2><?php print $titel;?></h2>

<?php // gesetzte Variablen (session+post) siehe kopf.php

// test password_verify
/*
$passwort = $_POST['passwort'];
$hash = '$2y$10$sE8YVSG1OiAZr9K7kEg8Texn3VpGVvL.fE2Kna2fjNgLc9NWYR23K';

if( password_verify($passwort, $hash) ) {
	echo 'Passwort stimmt!';
} else {
	echo 'Passwort ist falsch!';
}
*/

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
	zeige_loginformular(); ?> 
	<ul>
		<li><a href='<?php print PASSWORTVERGESSEN;?>'>Passwort vergessen?</a></li>
	</ul>
<?php }

/*
if ($_SESSION['login'] == 1)
	{
	header('Location: /neubuchen.php');
}
*/

fehlersuche ($_POST, 'Post');
fehlersuche ($_SESSION, 'Session');
require('includes/footer.php');

// begin functions

// $fehler = '' ist default und wird z.B. durch zeige_loginformular($formularfehler) überschrieben mit dem Inhalt von $formularfehler
function zeige_loginformular($fehler = '')
	{ ?> 
	<form method='POST' accept-charset='utf-8' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
		<legend>Login</legend>
	<?php if ($fehler) { ?> 
		<ul class='meldung'>
			<li><?php print implode("</li>\n\t<li>",$fehler);?></li>
		</ul>
	<?php } ?> 
			<table>
	<?php // Benutzername = Mailadresse
	input_text('mailadresse', 'Mailadresse');
	// Passwort
	input_passwort('passwort', 'Passwort');
	// Submit ?> 
				<tr>
	<?php input_submit('absenden','2','Login'); ?> 
				</tr>
			</table>
	<?php // Defaultwert für den Feldnamen ist 'abgeschickt';
	input_hidden(); ?> 
		</fieldset>
	</form>
<?php }

function validiere_loginformular() {
	global $pdo_handle;
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	// Positiv-Liste
	$sql="SELECT user as mailadresse, pass as hash, pass_changed
		FROM studio_user";
	// user, hashes und anderes aus der Datenbank holen, user, hash in array packen.
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(isset($result))
		{
		for ($i=0;$i<count($result);$i++)
			{
			// geht alle Benutzer und Pw durch, erstellt eine user-pass Liste
			// nur Kleinbuchstaben, siehe weiter unten
			$mailadresse=strtolower($result[$i]->mailadresse);
			$hash=$result[$i]->hash;
			$pass_changed=$result[$i]->pass_changed;
			// Array herstellen, wie ich es unten brauche
			// $array($user=>$hash)
			$user_pw_liste[$mailadresse]=$hash;
			// p_C für password changed
			$p_C[$mailadresse]=$pass_changed;
		}
	}
	// fehlersuche ($user_pw_liste, 'user-pass Array');
	// fehlersuche($pass_changed, 'validiere Login, Pass changed');
	// Nachricht an user
	$fehlermeldung = 'Bitte gib einen gültigen Benutzernamen und ein gültiges Passwort ein. <a href="' . PASSWORTVERGESSEN . '">Passwort vergessen?</a>';
	// nur fürs Entwickeln
	$fehlermeldung2 = 'user ist nicht im Array';
	$fehlermeldung3 = 'Login mit pw_verify hat nicht funktioniert.';
	
	
	// ab hier passwort des users mit hash vergleichen;
	// user existiert?
	// hash ist alt und nur mit md5 gültig?
	// hash ist neu und gültig?
	// hash ist neu und gültig und muss rehashed werden (neue PHP Version mit neuem PASSWORD_DEFAULT)
	// Fehler ins $fehler-Array schreiben.
	// neuen hash in die DB schreiben.
	
	if (isset($_POST['mailadresse']) && isset($user_pw_liste))
		{
		// user, passwort, hash
		// Die Leute geben ihre Mailadresse auch manchmal mit Großbuchstaben ein.
		$user = strtolower($_POST['mailadresse']);
		$passwort = $_POST['passwort'];
		$hash = $user_pw_liste[$user];
		// existiert der User?
		if (!array_key_exists($user, $user_pw_liste)) {
			$fehler[] = $fehlermeldung2;
			return $fehler;
		}
	// hash ist neu und gültig?
	if( password_verify($passwort, $hash) ) {
		// Passwort stimmt
		fehlersuche($hash, 'Passwort stimmt, pw verifiziert 2');
		// wenn PASSWORD_DEFAULT sich ändert (je nach PHP-Version), muss der hash neu berechnet und in der Datenbank gespeichert werden.
		rehash ($user, $passwort, $hash);
		} else {
			// Passwort ist falsch
			fehlersuche($hash, 'Passwort stimmt NICHT, pw verifiziert 3');
			// letzte Chance mit md5
			$ok = passwort_verify_md5($passwort, $hash);
			if ($ok) {
				rehash ($user, $passwort, $hash);
				return;
			} else {
			$fehler[] = 'Auch md5-Hash nicht gültig';
			return $fehler;
			}
		}
	$wait=$_SESSION['sleep']=$_SESSION['sleep']+0.1;
	// die nächste Zeile wenn fertig wieder auskommentieren.
	// sleep(intval($wait));
	// fehlersuche($wait);
	return $fehler;
	}
}

function verarbeite_loginformular()
	{
	// Muss das Passwort geändert werden?
	global $pdo_handle;

	$user = strtolower($_POST['mailadresse']);
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
	// wichtig: beide Arrays müssen denselben key haben; also, wenn 'mailadresse' bei $_SESSION, dann auch 'mailadresse' bei $_POST. Brauche ich für die Rückgabe von bereits eingegebenen Werten in Formularen.
	// mailadresse = Mailadresse
	$_SESSION['mailadresse'] = $_POST['mailadresse'];
	// mailadresse eingeloggt
	$_SESSION['login'] = 1;
	$_SESSION['userid'] = $userid;
	$_SESSION['vorname'] = $vorname;
	$_SESSION['name'] = $name;
	$_SESSION['userpreis'] = $userpreis;
	$_SESSION['admin'] = $admin;
	// die Anti-Hack-Zeitschaltuhr abstellen
	$_SESSION['sleep'] = 0.5; ?> 
	<p>login verarbeitet. <?php print $_SESSION['login'];?> (session-login)</p>
	<p>Der Login war erfolgreich! Hallo <?php print htmlentities($vorname);?> <?php print htmlentities($name);?>. (Mail: <?php print htmlentities($user);?>)</p>
<?php }

function rehash($user, $passwort, $hash) {
	global $pdo_handle;
	if( password_needs_rehash($hash, PASSWORD_DEFAULT)) {
		// Passwort neu hashen
		fehlersuche($passwort, 'needs rehash');
		$newhash = password_hash($passwort, PASSWORD_DEFAULT);
		// den alten gespeicherten Hash in der Datenbank durch den neuen ersetzen 
		fehlersuche($newhash, 'Neuer Hash');
		$sql = "UPDATE studio_user
			SET pass = :dbpass
			WHERE user = :user
			LIMIT 1";
		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':dbpass', $newhash);
		$stmt -> bindParam(':user', $user);
		$ok = $stmt -> execute();
		// später löschen:
		if ($ok) {  
			fehlersuche($ok, '<p>Neuer Hash (nach password_needs_rehash) in der Datenbank.</p>');
		} else {
			fehlersuche($ok, '<p>Neuer Hash NICHT in der Datenbank.</p>');
		}
	} else {
		fehlersuche($ok,'<p>rehash false</p>');
	}
}

// alte hashes prüfen
function passwort_verify_md5($passwort, $hash) {
	if ($hash == md5($passwort)) {
	return 1;
	} else {
	return 0;
	}
}

function validiere_loginformular_alte_version()
	{
	global $pdo_handle;
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);
	// Positiv-Liste
	$sql="SELECT user as mailadresse, pass as hash, pass_changed
		FROM studio_user";
	// user, hashes und anderes aus der Datenbank holen, user, hash in array packen.
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(isset($result))
		{
		for ($i=0;$i<count($result);$i++)
			{
			// geht alle Benutzer und Pw durch, erstellt eine user-pass Liste
			// nur Kleinbuchstaben, siehe weiter unten
			$mailadresse=strtolower($result[$i]->mailadresse);
			$hash=$result[$i]->hash;
			$pass_changed=$result[$i]->pass_changed;
			// Array herstellen, wie ich es unten brauche
			// array($user=>$hash)
			$user_pw_liste[$mailadresse]=$hash;
			// p_C für password changed
			$p_C[$mailadresse]=$pass_changed;
		}
	}
	// fehlersuche ($user_pw_liste, 'user-pass Array');
	// fehlersuche($pass_changed, 'validiere Login, Pass changed');
	// Nachricht an user
	$fehlermeldung = 'Bitte gib einen gültigen Benutzernamen und ein gültiges Passwort ein. <a href="' . PASSWORTVERGESSEN . '">Passwort vergessen?</a>';
	// nur fürs Entwickeln
	$fehlermeldung2 = 'user ist nicht im Array';
	$fehlermeldung3 = 'Login mit pw_verify hat nicht funktioniert.';
	if (isset($_POST['mailadresse']) && isset($user_pw_liste))
		{
		// Sicherstellen, dass der Benutzername gültig ist
		// Die Leute geben ihre Mailadresse auch manchmal mit Großbuchstaben ein.
		$user = strtolower($_POST['mailadresse']);
		$hash = $user_pw_liste[$user];
		fehlersuche($_POST['mailadresse'], 'Post Benutzer 1');
		if (! array_key_exists($user, $user_pw_liste))
			{
			$fehler[] = $fehlermeldung2;
		} elseif ($hash != md5($_POST['passwort']))
			// $user wegen strtolower, s.o., (eigentlich $_POST['mailadresse'])
			// Prüfen, ob das Passwort korrekt ist
			// gespeichertes Passwort = $user_pw_liste[$_POST['mailadresse']];
			{
			fehlersuche($user_pw_liste, 'md5($hash) nicht erfolgreich');
			// neue Passwortfunktion ab hier; $fehler soll nur ausgelöst werden, 
			// wenn jetzt auch der neue Hash nicht mit pass übereinstimmt.
			fehlersuche($user, 'User');
			fehlersuche($hash, 'Hash');
			if( password_verify($_POST['passwort'], $hash) ) {
				// Passwort stimmt!
				fehlersuche($hash, 'Passwort stimmt, pw verifiziert 2');
				// neue Passwort-Funktionen
				// wenn PASSWORD_DEFAULT sich ändert (je nach PHP-Version), muss der hash neu berechnet und in der Datenbank gespeichert werden.
				rehash ($user, $_POST['passwort'], $hash);
			} else {
				// echo 'Passwort ist falsch!';
				// Fehlermeldung gleich+wird ggf. überschrieben, erlaubt keine Rückschlüsse
				fehlersuche($hash, 'Passwort stimmt NICHT, pw verifiziert 3');
				$fehler[] = $fehlermeldung3;
			}
		}
	} else	{
		$fehler[1] = $fehlermeldung . ' ERROR in validiere_loginformular: else+debug';
	}
	// oben der alte md5 Login. Wenn bis hier kein Fehler aufgetreten ist, ist user-pass ok. 
	$wait=$_SESSION['sleep']=$_SESSION['sleep']+0.1;
	// die nächste Zeile wenn fertig wieder auskommentieren.
	// sleep(intval($wait));
	// fehlersuche($wait);
	return $fehler;
}


?>
