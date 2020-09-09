<?php
$titel = "Meine Rechnungen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');


$link = connect();
session_start();
session_regenerate_id(true);
require('includes/head.php');
require('includes/kopf.php');
require('includes/navi.php'); ?> 

<h2><?php print $titel;?></h2>

<?php // siehe kopf.php
logincheck();

if ($_SESSION['login'] == 1)
	{
	global $pdo_handle; ?> 
	<h3>Rechnungsübersicht</h3>
	<?php $user = $_SESSION['mailadresse'];
	$userid = $_SESSION['userid'];
	// Abfragen: Buchungen eines Monats, davon die Preise, Anzahl, Summe
	// ausrechnen per PHP für die Monatsrechung
	$bereich = strtotime('-1 month', time());
	$rechnungsjahr = date('Y', $bereich);
	$rechnungsmonate = array(
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
		'12'
	);
	foreach($rechnungsmonate as $schluessel => $rechnungsmonat)
		{
		$_SESSION['rechnungsjahr'] = $rechnungsjahr;

		$sql = "select preis as ' &euro;/Std. ',
			DATE_FORMAT(begintime, '%d.%m.%Y %H:%i')as Beginn,
			DATE_FORMAT(endtime, '%d.%m.%Y %H:%i')as Ende,
			TIME_TO_SEC(TIMEDIFF(endtime,begintime)) as sec,
			TIMEDIFF(endtime,begintime) as Dauer,
			TIME_TO_SEC(TIMEDIFF(endtime,begintime))*preis/3600 as Betrag
			from studio_buchung
			where DATE_FORMAT(begintime, '%Y-%m') = :format_begintime
			AND userID = :userid
			order by begintime";


		// pdo_out($pdo_handle,$sql, "Einzelaufstellung $rechnungsmonat/$rechnungsjahr");
		$format_begintime = "$rechnungsjahr-$rechnungsmonat";
		fehlersuche($format_begintime);
		$stmt = $pdo_handle -> prepare($sql);
		$stmt -> bindParam(':userid', $userid);
		$stmt -> bindParam(':format_begintime',$format_begintime);
		$stmt -> execute();
		// $spalten_anzahl = $stmt->columnCount();
		$result = $stmt->fetchAll(PDO::FETCH_OBJ);
		// out($result);
		$stmt -> execute();
		if ($result) $columnkeys = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
		// out($columnkeys);
		$caption = "Buchungen $rechnungsmonat/$rechnungsjahr";
		$gesamtdauer = 0;
		$summe = 0;
		if ($result)
			{
			// 2 Felder für gesamtdauer und summe
			$colspan = count($columnkeys) - 2; ?> 
			<table class='rahmen'>
				<caption><?php print $caption;?></caption>
				<tr>
				<?php for ($i = 0; $i < count($columnkeys); $i++)
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
					<?php  ?> <?php }
					$gesamtdauer += $result[$i]->sec;
					$summe += $result[$i]->Betrag; ?> 
					</tr>
				<?php } ?> 
				<tr>
					<td colspan='<?php print $colspan;?>'>Summe:</td>
					<td><?php print $gesamtdauer/3600;?> Std.</td>
					<td><?php print sprintf('%1.2f', $summe);?> €</td>
				</tr>
			</table>
			<?php $gesamtdauer = 0;
			$summe = 0;
		} else { ?> 
			<p>Keine <?php print $caption;?>.</p>
		<?php }
	unset ($result);
	}
}
fehlersuche ($_POST);
fehlersuche ($_SESSION);

require('includes/footer.php');
?>
