<?php
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
date_default_timezone_set('Europe/Berlin');

function buchung_mailen($userid,$buchungsbeginn,$buchungsende,$text='Buchung')
	{
	// Zusätzlich die Buchung mailen
	// MAILADRESSE siehe definitions.php

	// Welchen Betreff soll die Mail erhalten?
	$strSubject  = TITEL;
	// wie heißt der User (Reply-Mailadresse)
	$user = $_SESSION['benutzer'];
	$vorname = $_SESSION['vorname'];
	$name = $_SESSION['name'];
	// Mail-Layout
	$header = "From: " . $user . "\nReply-To: " . $user . "\nContent-type: text/plain; charset=ISO-8859-1\n";
	$mailtext = $text . strftime(' von %A, %x, %H:%M Uhr ',$buchungsbeginn) . strftime('bis %A, %x, %H:%M Uhr',$buchungsende) . ", $vorname $name.";
	// abschicken
	fehlersuche("Header: $header<br>\nAn: " . MAILADRESSE . "<br>\nBetreff: $strSubject<br>\nText: $mailtext<br>",'Mailtext');
	mail(MAILADRESSE, $strSubject, $mailtext, $header) or die ("<p>Die Mail konnte nicht versendet werden. </p>");
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
    { ?> 
     <pre class='fehlersuche'>
     <span><?php print $info ?>: <?php print_r($var); ?></span>
     </pre>
<?php }

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

function pdo_out($pdo_handle,$sql,$caption = 'Tabelle')
    {
    $stmt = $pdo_handle -> query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->execute();
    $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC)); 
    ?> 

    <table class = "pdo_out">
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

function pdo_result_out($result,$columnkeys,$caption = 'Tabelle')
    { ?> 
    <table class = "pdo_out">
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

function out($var, $name = 'Var: ')
    {
    if (is_array($var))
        { ?> 
        <pre>Array <?php print $name; 
        print_r($var);?></pre>
    <?php } else { ?> 
    <p><?php print $name . ' ' . $var;?></p>
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
    // print "<p class=\"rechts\"><a href=\"" . LOGIN . "\">Login</a></p>";
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
function input_text($feldname, $label= 'Textfeld')
    {
    print "\t<tr>\n\t<td class=\"rechts\">$label: </td>\n";
    if (iswech() && isset($_POST['benutzer']))
        {
        print "\t<td><input type=\"text\" name=\"$feldname\" value=\"" . htmlentities($_POST[$feldname]) .  "\"></td>\n";
    } elseif (isset($_SESSION['benutzer']) && isset($_SESSION[$feldname]))
        {
        print "\t<td><input type=\"text\" name=\"$feldname\" value=\"" . htmlentities($_SESSION[$feldname]) .  "\"></td>\n";
    } else {
        print "\t<td><input type=\"text\" name=\"$feldname\"></td>\n";
    }
    print "\t</tr>\n\n";
}

// Eine Selectfeld ausgeben
function input_select($feldname, $timedefaults, $optionen)
    {
    print "\t\t<select size=\"1\" name=\"$feldname\" id=\"$feldname\">\n";
    // braucht Wert und Label, schon sortiert
    foreach ($optionen as $wert => $label)
        {
        if (isset($_POST[$feldname]) && $_POST[$feldname] == $wert)
            {
            // print "<option> Ausgabe: $_POST[$feldname] </option>";
            print "\t\t\t<option selected value=\"$wert\">$label</option>\n";
        } elseif (!isset($_POST[$feldname]) && $timedefaults[$feldname] == $wert)
            {
            // print "<option> Ausgabe: $timedefaults[$feldname] </option>";
            print "\t\t\t<option selected value=\"$wert\">$label</option>\n";
        } else {
            print "\t\t\t<option value=\"$wert\">$label</option>\n";
        }
    }
    print "\t\t</select>\n";
}

// Passwort-Feld ausgeben
function input_passwort($feldname, $label = 'Passwort')
    {
    // $werte = $_POST[$feldname]; brauche ich beim Passwort nicht
    print "\t<tr>\n";
    print "\t<td class=\"rechts\">$label: </td>\n";
    print "\t<td><input type=\"password\" name=\"$feldname\"></td>\n";
    print "\t</tr>\n\n";
}

// Einen Absenden-Button ausgeben
function input_submit($feldname, $colspan, $label = 'Absenden')
    {
    if ($colspan > 0)
        {
        print "\t<td colspan=\"$colspan\"></td>\n";
    }
    print "\t<td><input type=\"submit\" name=\"$feldname\" value=\"$label\"></td>\n";
}

// Einen Absenden-Button ausgeben mit <span>
function input_submit_p($feldname, $label = 'Absenden')
    {
    print "\t<span><input type=\"submit\" name=\"$feldname\" value=\"$label\">\n";
}

// das versteckte Formularfeld ausgeben
function input_hidden($feldname = 'abgeschickt')
    {
    print "<input type=\"hidden\" name=\"$feldname\" value=\"1\">\n";
}

// die folgenden noch bearbeiten
// Ein mehrzeiliges Textfeld ausgeben
function input_textarea($elementname, $werte)
    {
    print '<textarea name="' . $elementname .'">';
    print htmlentities($werte[$elementname]) . '</textarea>';
}

// Einen Radiobutton oder eine Checkbox ausgeben
// input type="radio" oder
// input type="checkbox"
function input_radiocheck($typ, $feldname, $werte, $feldwert)
    {
    print '<input type="' . $typ . '" name="' . $feldname .'" value="' . $feldwert . '" ';
    // fehlersuche($feldname, 'Feldname');
    // fehlersuche($werte,'Werte-Array');
    if (isset($feldwert) && isset($werte) && isset($werte[$feldname]))
        {
        if ($feldwert == $werte[$feldname])
            {
            print ' checked="checked"';
        }
    }
    print '>';
}
