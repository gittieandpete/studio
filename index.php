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

// siehe functions.php
if (!isset($_SESSION['sleep']))
	{
	$_SESSION['sleep']=0.5;
	fehlersuche($_SESSION['sleep']);
}

if (iswech() && $_SESSION['login'] == 0)
	{
	// password_check() brauche ich auch für changepass.php, siehe includes/functions
	if ($formularfehler = password_check())
		{
		fehlersuche('<p>1. zeige login mit Fehlern</p>');
		zeige_loginformular($formularfehler);
	} else {
		fehlersuche('<p>2. else verarbeite login</p>');
		verarbeite_loginformular();
		header('Location: /neubuchen.php');
	}
} elseif ($_SESSION['login'] == 0 )
	{
	fehlersuche('<p>3. elseif zeige login</p>');
	zeige_loginformular(); ?> 
	<ul>
		<li><a href='<?php print PASSWORTVERGESSEN;?>'>Passwort vergessen?</a></li>
	</ul>
<?php }


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


function verarbeite_loginformular()
	{
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
	// Der Session den Benutzernamen hinzufügen
	// wichtig: beide Arrays müssen denselben key haben; also, wenn 
	// 'mailadresse' bei $_SESSION, dann auch 'mailadresse' bei $_POST. 
	// Brauche ich für die Rückgabe von bereits eingegebenen Werten in Formularen.
	$_SESSION['mailadresse'] = $_POST['mailadresse'];
	// mailadresse eingeloggt
	$_SESSION['login'] = 1;
	$_SESSION['userid'] = $userid;
	$_SESSION['vorname'] = $vorname;
	$_SESSION['name'] = $name;
	$_SESSION['userpreis'] = $userpreis;
	$_SESSION['admin'] = $admin;
	// die Anti-Hack-Zeitschaltuhr abstellen
	$_SESSION['sleep'] = 0.5;
}
?>
