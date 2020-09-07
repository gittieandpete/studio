<?php
$titel = "Datenbank-Entwurf";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php');

if ($_SESSION['login'] == 0) print "<p>Bitte <a href=\"" . LOGIN . "\">logge</a> dich <a href=\"" . LOGIN . "\">ein</a>!</p>";
if ($_SESSION['login'] == 1)
	{
	print "<h2>$titel</h2>";
	print <<<HTML
	<pre>
	create table studio_user
		(
		id INT NOT NULL auto_increment,
		PRIMARY KEY(id),
		-- user = mailadresse
		user varchar(100),
		vorname varchar(100),
		name varchar(100),
		pass varchar(50),
		pass_changed smallint unsigned default '0',
		userpreis decimal(7,2) default '2.50',
		admin smallint unsigned default '0',
		ts TIMESTAMP not null
	);
	create table studio_buchung (
		id INT NOT NULL auto_increment,
		PRIMARY KEY(id),
		userID int NOT NULL,
		begintime datetime NOT NULL,
		endtime datetime NOT NULL,
		preis decimal (7,2) unsigned not null default '2.50',
		ts TIMESTAMP not null
	);

	admin-Werte siehe admin.php

	-- ein paar Testuser eingeben
	insert into studio_user (user, vorname, name, pass, pass_changed) values('petermueller@c-major.de', 'Peter', 'Müller', '123', '1');
	insert into studio_user (user, vorname, name, pass, pass_changed) values('peter.mueller@c-major.de', 'Peter', 'Müller', '123', '1');
	insert into studio_user (user, vorname, name, pass, pass_changed) values('kai@web.de', 'Kai', 'Buhr', '123', '1');
	insert into studio_user (user, vorname, name, pass, pass_changed) values('jakob@c-major.de', 'Jakob', 'Köhler', '123', '1');
	insert into studio_user (user, vorname, name, pass) values ('postfach.christinekoehler.de','Christine','Köhler','123');
	-- ein paar Buchungsdaten eingeben
	insert into studio_buchung (userID, begintime, endtime) values('2', '2010-12-01 08:30:00', '2010-12-01 10:30:00');
	insert into studio_buchung (userID, begintime, endtime) values('2', '2010-12-01 15:30:00', '2010-12-01 16:30:00');
	insert into studio_buchung (userID, begintime, endtime) values('2', '2010-12-01 21:30:00', '2010-12-01 22:00:00');
	-- pass mit md5 verschlüsseln
	update studio_user set pass = md5(pass) where pass = '123';

	Und Testbuchungen:

	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-01-22 09:00:00', '2010-01-22 10:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-01-23 12:30:00', '2010-01-23 13:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-02-22 03:00:00', '2010-02-22 04:30:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-02-23 14:30:00', '2010-02-23 15:30:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-03-17 14:00:00', '2010-03-17 22:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-03-21 02:30:00', '2010-03-21 04:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-04-20 05:00:00', '2010-04-20 05:30:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-04-20 06:00:00', '2010-04-20 07:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-05-20 07:00:00', '2010-05-20 08:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-05-20 03:00:00', '2010-05-20 05:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-06-24 10:00:00', '2010-06-24 22:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-06-22 22:30:00', '2010-06-22 23:30:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-07-22 18:00:00', '2010-07-22 18:30:00');
	insert into studio_buchung (userID, begintime, endtime) values ('2', '2010-08-01 02:00:00', '2010-08-01 23:00:00');
	insert into studio_buchung (userID, begintime, endtime) values ('7', '2013-10-30 16:30:00', '2013-10-30 21:30:00');



	</pre>
HTML;
}

require('includes/footer.php');
?>
