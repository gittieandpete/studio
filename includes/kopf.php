<div class="kopfzeile">
<?php
// gesetzte Variablen
if (!isset ($_SESSION['login']))
    {
    $_SESSION['login'] = 0;
}

if (!isset($_POST['logout']))
    {
    $_POST['logout'] = 0;
}
if ($_SESSION['login'] == 1)
    {
    if ($_POST['logout'] == 1)
        {
        if ($formularfehler = validiere_logoutformular())
            {
            fehlersuche('zeige logout','Fall 1, Session Login');
            zeige_logoutformular($formularfehler);
        } else	{
        fehlersuche('verarbeite logout','Fall 2, Session Login');
        verarbeite_logoutformular();
        }
    } else {
    fehlersuche('login = 1','Fall 3, Session Login');
    zeige_logoutformular();
    }
}
?>
<h1 class='logo' id='kopf'><?php print TITEL;?></h1>
</div> <!--class="kopfzeile"-->