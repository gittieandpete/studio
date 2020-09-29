<?php
$titel = "Terminbot";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


connect ();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php');

// Kopiert von lukas.

logincheck();
	
if ($_SESSION['admin']>3)
	{ ?> 
	<h2><?php print $titel;?></h2>
	<?php 
	// maximum of bookings ahead, number of weeks to check, see function check_if_new_bookings
	$range = 8;
	print '<p>checks new bookings. If yes, call function make_new_bookings, 
		then again check bookings (range ' . $range . ' weeks)</p>';
	check_if_new_bookings();
}


require('includes/footer.php');

# begin functions
function make_new_bookings ($lastbooking) {
	global $pdo_handle;
	// mit Zeiten rechnen
	$days = 24*60*60;
	$hours = 60*60;
	$minutes = 60;
	/*
	Daten: 
	           	Wochentag  	Beginn 	Ende
	$begin1  	Donnerstag 	10  	18
	*/
	print 'Letzter Eintrag: ' . date('d. m. Y H:i', $lastbooking) . '<br>';
	$nextbookingday = strtotime('next Thursday',$lastbooking);
	// print date('d.m.Y H:i', $nextbookingday) . '<br>';
	// Die Anfangszeit setzen, also hier 10:00 Uhr, in Array schreiben:
	$termine['begin'][0] = $nextbookingday + 10*$hours;
	print 'Nächster Eintrag: ' . date('d.m.Y H:i', $termine['begin'][0]) . '<br>';
	// Ende setzen, also 18 Uhr
	$termine['end'][0] = $nextbookingday + 21*$hours;
	// weitere Termine bei Bedarf
	// $termine['begin'][1] = $nextbookingday + 1*$days + 10*$hours;
	// $termine['end'][1] = $nextbookingday + 1*$days + 12*$hours;
	// Der Tag, die Anfangs- und Endzeit sind ok, jetzt den Termin buchen. 
	// mysql -u web330 -p usr_web330_1
	// Buchung für Christine, userID 3
	// print_r($termine);
	$sql = 'INSERT 
		INTO studio_buchung (userID, begintime, endtime) 
		VALUES (3,:begintime,:endtime)';
	$stmt = $pdo_handle -> prepare($sql);	
	$stmt -> bindParam(':begintime', $begin);
	$stmt -> bindParam(':endtime', $end);
	// zählt Anzahl der Beginnzeiten
	for ($i=0;$i<count($termine['begin']);$i++) {
		$begin = date('Y-m-d H:i:s', $termine['begin'][$i]);
		$end = date('Y-m-d H:i:s', $termine['end'][$i]);
		$stmt -> execute();
	} 
	check_if_new_bookings();
}

function check_if_new_bookings() {
    global $pdo_handle,
		$range;
	// Den letzten Termin prüfen. Liegt er zu nah am aktuellen Datum, sollen neue Termine eingetragen werden.
	// geht davon aus, dass ich nicht andere Termine vor dieser Zeit im Voraus gebucht habe.
	// da ich nicht prüfe, ob es Termine von anderen zu dieser Zeit gibt, nehme ich den letzten gebuchten Termin.
	// mysql -u web330 -p usr_web330_1
	$query = 'SELECT 
		UNIX_TIMESTAMP(begintime) AS unixtime 
		FROM studio_buchung 
		WHERE begintime = 
			(SELECT 
			MAX(begintime) 
			FROM studio_buchung)';
	$stmt = $pdo_handle -> query($query);
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$lastbooking = $result[0]['unixtime'];
	$today = time();
	print 'Heute: ' . date('d.m.Y H:i', $today) . '<br>';
	print 'Letzte bisherige Buchung: ' . date('d.m.Y H:i', $lastbooking) . '<br>';
	// um eine Variable in strtotime aufzulösen, use double quotes
	if (strtotime("+$range weeks",$today) < $lastbooking) {
		print 'Es gibt noch genug Termine.<br>';
	} else {
		// put here the code for new lessons
		print 'Mache neue Termine.<br>';
		make_new_bookings($lastbooking);
	}
}

?>
