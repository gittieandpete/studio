<?php
// wieviel Jahre anzeigen?
$jahreanzeigen = 2;
$halbestunden = array(
	'00',
	30 => '30'
);
$stunden = array(
	'00',
	'01',
	'02',
	'03',
	'04',
	'05',
	'06',
	'07',
	'08',
	'09',
	'10',
	'11',
	'12',
	'13',
	'14',
	'15',
	'16',
	'17',
	'18',
	'19',
	'20',
	'21',
	'22',
	'23'
);
$tage = array(
	1 => '01',
	'02',
	'03',
	'04',
	'05',
	'06',
	'07',
	'08',
	'09',
	'10',
	'11',
	'12',
	'13',
	'14',
	'15',
	'16',
	'17',
	'18',
	'19',
	'20',
	'21',
	'22',
	'23',
	'24',
	'25',
	'26',
	'27',
	'28',
	'29',
	'30',
	'31'
);
$monate = array(
	1 => 'Januar',
	'Februar',
	'März',
	'April',
	'Mai',
	'Juni',
	'Juli',
	'August',
	'September',
	'Oktober',
	'November',
	'Dezember'
);
$wochentage = array(
	'Sonntag',
	'Montag',
	'Dienstag',
	'Mittwoch',
	'Donnerstag',
	'Freitag',
	'Samstag'
);
foreach ($monate as $schluessel => $wert)
	{
	$monatenumerisch[$wert] = $schluessel;
}

$jahre = array();
for ($jahr = date('Y'), $max_jahr = date('Y') + $jahreanzeigen; $jahr < $max_jahr; $jahr++)
	{
	$jahre[$jahr] = $jahr;
}