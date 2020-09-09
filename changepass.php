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
require('includes/navi.php'); ?> 

<h2><?php print $titel;?></h2>

<?php logincheck();

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

fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');

// Beginn functions

// $fehler = '' ist default und wird z.B. durch zeige_formular($formularfehler) überschrieben mit dem Inhalt von $formularfehler
function zeige_formular($fehler = '')
	{ ?> 
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
		<legend>Passwort ändern</legend>
	<?php if ($fehler) { ?> 
		<ul class='meldung'>
			<li><?php print implode("</li>\n\t<li>",$fehler);?></li>
		</ul>
	<?php }  ?> 
	<table>
	<?php // Benutzername
	input_text('benutzer', 'Mailadresse');
	// altes Passwort
	input_passwort('pass_old', 'altes Passwort');
	// Passwort
	input_passwort('passwort', 'neues Passwort');
	input_passwort('passwort_control', 'neues Passwort'); ?> 
		<tr>
	<?php // Submit $feldname, $colspan, $label, input_submit liefert <td></td>
	input_submit('absenden','1','Passwort ändern'); ?> 
		</tr>
	</table>
	<?php input_hidden(); ?> 
	</fieldset>
	</form>
<?php }

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
	$user = $_SESSION['mailadresse'];
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
		{ ?> 
		<p>Hallo <?php print $_SESSION['vorname'];?>, <br> <strong>das Passwort</strong> für <?php print $user;?> <strong>wurde erfolgreich geändert</strong>!</p>
	<?php } else {  ?> 
		<p>Ein Fehler ist passiert (changepass.php). Bitte versuch es noch mal oder wende dich per Mail an <a href="mailto:peter.mueller@c-major.de">peter.mueller@c-major.de</a>.</p>
		<?php zeige_formular();
	}
}

?>
