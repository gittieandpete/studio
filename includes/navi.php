
<a href="#linkliste" id="flip_nav" class="flip_hilfe">Gehe zur Navigation</a>
<nav id="linkliste">
<ul class="menu">

<?php

	//function menue ($adresse,$ankertext,$linktitel='Link')
	menue (LOGIN, 'Login', 'Startseite');
	menue (NEUBUCHEN, 'Neu buchen','Neue Buchung anlegen');
	menue (MEINEBUCHUNGEN, 'Meine Buchungen', '�bersicht �ber meine zuk�nftigen Buchungen');
	menue (MEINERECHNUNGEN, 'Meine Rechnungen', '�bersicht �ber meine bisherigen Ausgaben dieses Jahr');
	menue (BUCHUNGAENDERN, 'Buchung �ndern', 'Eine bestehende Buchung �ndern die noch nicht begonnen hat');
	menue (BUCHUNGLOESCHEN, 'Buchung l�schen', 'Eine Buchung l�schen die noch nicht begonnen hat');
	menue (KALENDER, 'Kalender', '�bersicht �ber alle Termine eines Monats');
	menue (PASSWORTAENDERN, 'Passwort �ndern');

if (isset($_SESSION['benutzer']) && $_SESSION['admin']>0)
	{
	if ($_SESSION['admin']>3)
		{
		menue (ADMINBUCHUNGEN, 'Alle Buchungen', '...aller User');
	}
	if ($_SESSION['admin']%4>1)
		{
		menue (ADMINRECHNUNGEN, 'Alle Rechnungen', 'monatlich f�r dieses Jahr');
	}
	if ($_SESSION['admin']%2>0)
		{
		menue (ADMINUSER, 'User verwalten', 'User hinzuf�gen');
	}
}
?>

</ul> <!-- menu -->
<a id="flip_top" href="#">&#8593; Top</a>
</nav> <!--id linkliste-->

