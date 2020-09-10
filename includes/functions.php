<?php
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
date_default_timezone_set('Europe/Berlin');

function buchung_mailen($userid,$buchungsbeginn,$buchungsende,$text='Buchung')
	{
	// Zusätzlich die Buchung mailen
	// MAILADRESSE siehe definitions.php

	// Welchen Betreff soll die Mail erhalten?
	$strSubject = TITEL;
	// wie heißt der User (Reply-Mailadresse)
	$user = $_SESSION['mailadresse'];
	$vorname = $_SESSION['vorname'];
	$name = $_SESSION['name'];
	// Mail-Layout
	$header = "From: " . $user . "\nReply-To: " . $user . "\nContent-type: text/plain; charset=UTF-8\n";
	$mailtext = $text . strftime(' von %A, %x, %H:%M Uhr ',$buchungsbeginn) . strftime('bis %A, %x, %H:%M Uhr',$buchungsende) . ", $vorname $name.";
	// abschicken
	fehlersuche("Header: $header<br>\nAn: " . MAILADRESSE . "<br>\nBetreff: $strSubject<br>\nText: $mailtext<br>",'Mailtext');
	$sent = mail(MAILADRESSE, $strSubject, $mailtext, $header);
	if (!$sent)
		{ ?> 
		<p>Die Mail konnte nicht versendet werden.</p>
	<?php }
}


// PDO benutzen
function connect ()
	{
	global $pdo_handle;
	$dsn = 'mysql:host=localhost;dbname=' . DATENBANK . ';charset=utf8';
	// default fetch-mode ist object (FETCH_OBJ)
	$opt = array(
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
	);
	$pdo_handle = new PDO($dsn,USER,PASSWORT,$opt);
}

function fehlersuche($var,$info='Debug')
    { 
    // Fehlersuche an oder aus, 1 oder 0
    $fehlersuche = 0;
    if($fehlersuche)
		{ ?> 
     <pre class='fehlersuche'>
     <span><?php print $info ?>: <?php print_r($var); ?></span>
     </pre>
	<?php }
}

// bezieht sich auf das hidden-Feld im Formular
function iswech()
    {
    if (isset ($_POST['abgeschickt']) && $_POST['abgeschickt'] == 1)
        {
        return true;
    }
    return false;
}

function logincheck()
	{
	if ($_SESSION['login'] == 0) 
		{ ?> 
		<p>Bitte <a href='<?php print LOGIN;?>'>logge dich ein</a>!</p>
	<?php }
}

function menue ($adresse,$ankertext,$linktitel='Link')
    {
    if ($adresse == $_SERVER['REQUEST_URI'])
        { ?> 
        <li class="aktuell"><?php print $ankertext;?></li>
    <?php }
    else
        { ?> 
        <li><a href="http://<?php print htmlspecialchars($_SERVER['HTTP_HOST']) . $adresse;?>" title="<?php print $linktitel;?>"><?php print $ankertext;?></a></li>
    <?php }
}

function password_changed_check() {
	// Muss das Passwort geändert werden?
	global $pdo_handle;

	$user = strtolower($_SESSION['mailadresse']);
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
	fehlersuche($p_c,'Passwort geändert?');
	if ($p_c == 0)
		{ ?> 
		<p>Dein Passwort sollte <a href='<?php print PASSWORTAENDERN;?>'>geändert</a> werden.</p>
	<?php }
}

function password_check() {
	// $fehler ist das Array, dass an die zeige_loginformular function zurückgegeben wird, falls welche auftreten.
	// Treten keine Fehler auf, wird das Formular verarbeitet.
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
	fehlersuche ($user_pw_liste, 'user-pass Array');
	// fehlersuche($pass_changed, 'validiere Login, Pass changed');
	// Nachricht an user
	$fehlermeldung = 'Bitte gib einen gültigen Benutzernamen und ein gültiges Passwort ein. <a href="' . PASSWORTVERGESSEN . '">Passwort vergessen?</a>';

	// function password_check, da ich das auch für changepass.php brauche.
	// die function braucht das
	// passwort des users mit hash vergleichen;
	// user existiert?
	// hash ist alt und nur mit md5 gültig?
	// hash ist neu und gültig?
	// hash ist neu und gültig und muss rehashed werden (neue PHP Version mit neuem PASSWORD_DEFAULT)
	// Fehler ins $fehler-Array schreiben.
	// neuen hash im Falle von rehash in die DB schreiben.
	
	if (isset($_POST['mailadresse']) && isset($user_pw_liste))
		{
		// user, passwort, hash
		// Die Leute geben ihre Mailadresse auch manchmal mit Großbuchstaben ein.
		$user = strtolower($_POST['mailadresse']);
		$passwort = $_POST['passwort'];
		fehlersuche($_POST['passwort'],'$_POST[passwort]');
		$hash = $user_pw_liste[$user];
		// existiert der User?
		if (!array_key_exists($user, $user_pw_liste)) {
			$fehler[] = $fehlermeldung;
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
			$fehler[0] = $fehlermeldung;
			return $fehler;
			}
		}
	$wait=$_SESSION['sleep']=$_SESSION['sleep']+0.1;
	// die nächste Zeile wenn fertig wieder auskommentieren.
	sleep(intval($wait));
	fehlersuche($wait);
	return $fehler;
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

function pdo_out($pdo_handle,$sql,$caption='Tabelle')
    {
    $stmt = $pdo_handle -> query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->execute();
    $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC)); 
    ?> 

    <table class='pdo_out'>
    <caption><?php print $caption;?></caption>
    <tr>
    <?php for ($i=0;$i<count($columnkeys);$i++)
        { ?> 
        <th><?php print $columnkeys[$i];?></th>
    <?php } ?> 
    </tr>
    <?php for ($i=0;$i<count($result);$i++)
        { ?> 
        <tr>
        <?php foreach ($result[$i] as $schluessel => $wert)
            { ?> 
            <td><?php print $wert;?></td>
        <?php } ?> 
        </tr>
    <?php } ?> 
    </table>
<?php }

function pdo_result_out($result,$columnkeys,$caption='Tabelle')
    { ?> 
    <table class='pdo_out'>
    <caption><?php print $caption;?></caption>
    <tr>
    <?php for ($i=0;$i<count($columnkeys);$i++)
        { ?> 
        <th><?php print $columnkeys[$i];?></th>
    <?php } ?> 
    </tr>
    <?php for ($i=0;$i<count($result);$i++)
        { ?> 
        <tr>
        <?php foreach ($result[$i] as $schluessel => $wert)
            { ?> 
            <td><?php print $wert;?></td>
        <?php } ?> 
        </tr>
    <?php } ?> 
    </table>
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
		fehlersuche('<p>needs_rehash false</p>');
	}
}

function validiere_logoutformular()
    {
    $fehler = array();
    $fehler = validiere_post($_POST,$fehler);
    if (isset($_POST['logout']))
        {
        if ($_POST['logout'] != 1)
            {
            $fehler = 'Der Logout hat nicht funktioniert.';
        }
    }
    return $fehler;
}

// prüft nur Länge und Null-string. Ändert nicht $_POST
function validiere_post($post,$fehler)
    {
    foreach($post as $key => $value)
        {
        if (strlen($value) > 300)
            {
            $fehler[] = "Die Eingabe " . htmlentities(substr($value, 0, 50)) . "... ist zu lang.";
        }
        if (strlen(trim($value)) == 0)
            {
            $fehler[] = "Bitte gib etwas ein!";
        }
        if (strlen($key) > 300 || strlen(trim($key)) == 0)
            {
            $fehler[] = "Bitte fülle das Formular richtig aus!";
        }
    }
    return $fehler;
}

function verarbeite_logoutformular()
    {
    foreach ($_SESSION as $key => $value)
        {
        unset($_SESSION[$key]);
    }
    foreach ($_POST as $key => $value)
        {
        unset($_POST[$key]);
    }
    $_SESSION['login'] = 0;
    session_destroy();
    fehlersuche($_SESSION, 'Session, verarbeite Logout');
}

function zeige_logoutformular()
    { ?> 
    <form class="logoutformular" method="POST" action="<?php print LOGIN;?>">
    <fieldset>
    <?php input_submit('abmelden','1','Logout');
    input_hidden('logout'); ?> 
    </fieldset>
    </form>
<?php }

###########################
#
# Formularhelfer
#
############################
// Textfeld, Passwortfeld, mit <tr> und zwei <td>s, select, submit ohne <tr>, hidden ohne alles
// Ein Textfeld ausgeben
function input_text($feldname, $label='Textfeld')
    { ?> 
    <tr><td class='rechts'><?php print $label;?>: </td>
    <?php 
    if (iswech() && isset($_POST['mailadresse']))
        { ?> 
        <td><input type='text' name='<?php print $feldname;?>' value='<?php print htmlentities($_POST[$feldname]);?>'></td>
    <?php } elseif (isset($_SESSION['mailadresse']) && isset($_SESSION[$feldname]))
        { ?> 
        <td><input type='text' name='<?php print $feldname;?>' value='<?php print htmlentities($_SESSION[$feldname]);?>'></td>
    <?php } else { ?> 
		<td><input type='text' name='<?php print $feldname;?>'></td>
    <?php } ?> 
    </tr>
<?php }

// Eine Selectfeld ausgeben
function input_select($feldname, $timedefaults, $optionen)
    { ?>
    <select size='1' name='<?php print $feldname;?>' id='<?php print $feldname;?>'>
    <?php // braucht Wert und Label, schon sortiert
    foreach ($optionen as $wert => $label)
        {
        if (isset($_POST[$feldname]) && $_POST[$feldname] == $wert)
            { ?> 
            <option selected value='<?php print $wert;?>'><?php print $label;?></option>
		<?php } elseif (!isset($_POST[$feldname]) && $timedefaults[$feldname] == $wert)
            { ?>
            <option selected value='<?php print $wert;?>'><?php print $label;?></option>
        <?php } else { ?> 
            <option value='<?php print $wert;?>'><?php print $label;?></option>
        <?php }
    } ?> 
    </select>
<?php }

// Passwort-Feld ausgeben
function input_passwort($feldname, $label='Passwort')
    { 
    // $werte = $_POST[$feldname]; brauche ich beim Passwort nicht ?> 
    <tr>
		<td class='rechts'><?php print $label;?>: </td>
		<td><input type='password' name='<?php print $feldname;?>'></td>
    </tr>
<?php }

// Einen Absenden-Button ausgeben
function input_submit($feldname, $colspan=1, $label='Absenden')
    { ?> 
    <tr>
		<td colspan='<?php print $colspan;?>' class='submit'><input type='submit' name='<?php print $feldname;?>' value='<?php print $label;?>'></td>
	</tr>
<?php }

// Einen Absenden-Button ausgeben mit <span>
function input_submit_p($feldname, $label='Absenden')
    { ?> 
    <span><input type='submit' name='<?php print $feldname;?>' value='<?php print $label;?>'></span>
<?php }

// das versteckte Formularfeld ausgeben
function input_hidden($feldname = 'abgeschickt')
    { ?> 
    <input type='hidden' name='<?php print $feldname;?>' value='1'>
<?php }

// Einen Radiobutton oder eine Checkbox ausgeben
// input type="radio" oder
// input type="checkbox"
function input_radiocheck($typ, $feldname, $werte, $feldwert)
    { ?> 
    <input type='<?php print $typ;?>' name='<?php print $feldname;?>' value='<?php print $feldwert;?>' 
    <?php if (isset($feldwert) && isset($werte) && isset($werte[$feldname]))
        {
        if ($feldwert == $werte[$feldname])
            { ?> 
             checked
        <?php }
    } // schließendes tag input ?>
    >
<?php }

