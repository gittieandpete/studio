<?php
$titel = "Userverwaltung";
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
logincheck();
if ($_SESSION['login'] == 1 && $_SESSION['admin']%2>0)
	{ ?> 
	<h2><?php print $titel;?></h2>
	<?php if (iswech())
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
$sql = "SELECT user as 'Mailadresse',
		vorname as 'Vorname',
		name as 'Name',
		userpreis as 'Preis',
		id as 'ID',
		pass as 'Passworthash',
		pass_changed
	FROM studio_user
	ORDER BY id";
pdo_out($pdo_handle,$sql, 'Eingetragene User');
print "<p>Das Default-Passwort ist '123'.</p>";
print '<p>User löschen geht direkt in der Datenbank.</p>';
}

fehlersuche ($_POST, 'table');
fehlersuche ($_SESSION, 'table');

require('includes/footer.php');


// Beginn functions.
function zeige_formular($fehler = '')
	{ ?> 
	<form method='POST' action='<?php print htmlspecialchars($_SERVER['PHP_SELF']);?>'>
		<fieldset>
			<legend>Neue User eintragen</legend>
			<?php if ($fehler) { ?> 
				<ul class='meldung'>
				<li><?php print implode("</li>\n\t<li>",$fehler);?></li>
				</ul>
			<?php }
		// input_text braucht eine Rahmen-Tabelle ?> 
			<table>
				<?php input_text('user', 'Mailadresse');
				input_text('name', 'Name');
				input_text('vorname', 'Vorname');
				input_text('userpreis', 'Preis pro Stunde');
				input_submit('neueruser','2','Neuen User anlegen');
				input_hidden(); ?> 
			</table>
		</fieldset>
	</form>
	<?php fehlersuche ($_POST);
	fehlersuche($fehler);
}

function validiere_formular()
	{
	global $pdo_handle;
	$fehler = array();
	$fehler = validiere_post($_POST,$fehler);

	if (!is_numeric($_POST['userpreis']))
		{
		$fehler[] = "Gib einen Preis ein!";
	}
	$preis = $_POST['userpreis'];
	if ($ok = strpos($preis,','))
		{
		$fehler[] = "Dezimalzeichen ist der Punkt(.)!";
	}
	fehlersuche ($_POST['userpreis'],'Postwert');
	// Die Leute geben ihre Mailadresse auch manchmal mit Großbuchstaben ein.
	$user = strtolower($_POST['user']);
	// simples selbstgestricktes Muster
	$muster = '/^[^@]+@[a-zA-Z0-9._\-]+\.[a-zA-Z]+$/';
	$at = preg_match($muster, $user);
	if (!$at)
		{
		$fehler[] = "Bitte gib eine gültige Mailadresse ein!";
	}
	// user=Mailadresse
	$sql = "SELECT user
		FROM studio_user";
	$stmt = $pdo_handle -> prepare($sql);
	$stmt -> execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	fehlersuche($result,'Mailadressen Suche');
	$dbliste = array();

	for ($i=0;$i<count($result);$i++)
		{
		foreach ($result[$i] as $schluessel => $wert)
			{
			$dbliste[] = $wert;
		}
	}
	fehlersuche($dbliste,'DBListe');
	// in der Tabelle stehen die Mailadressen mit kleinbuchstaben
	if(in_array($user,$dbliste))
		{
		$fehler[] = "Diese Mailadresse wurde schon eingetragen.";
	}

	return $fehler;
}

function verarbeite_formular()
	{
	global $pdo_handle;
	// print "<p>Verarbeite: Post</p>";
	// Die Leute geben ihre Mailadresse auch manchmal mit Großbuchstaben ein.
	$user = strtolower($_POST['user']);
	$vorname = $_POST['vorname'];
	$name = $_POST['name'];
	$userpreis = $_POST['userpreis'];
	fehlersuche ($_POST['userpreis'],'Postwert');
	$pass = md5('123');
	fehlersuche($pass,'md5 Wert von 123');
	$sql = "INSERT into studio_user (user, vorname, name, userpreis, pass)
		VALUES ('$user', '$vorname', '$name', '$userpreis', '$pass')";
	$stmt = $pdo_handle -> prepare($sql);
	$ok = $stmt -> execute();
	if ($ok)
		{ ?> 
		<p>Der neue User wurde eingetragen!</p>
	<?php } else {  ?> 
		<p>hat nicht funktioniert!</p>
	<?php }
}

?>
