<?php
$titel = "Meine Rechnungen";
require('includes/functions.php');
require('includes/definitions.php');
require('../../../files/c-major/login_web330.php');
connect();
session_start();
session_regenerate_id(true);

require('includes/fpdf.php');

class PDF extends FPDF
{

function ImprovedTable($header,$data)
{
    //Column widths
    $w=array(15,30,30,20,20);
    //Header
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C');
    $this->Ln();
    //Data
    foreach($data as $row)
    {
        $this->Cell($w[0],6,$row[0],'LR',0,'R');
        $this->Cell($w[1],6,$row[1],'LR');
        $this->Cell($w[2],6,$row[2],'LR');
        $this->Cell($w[3],6,$row[3],'LR',0,'R');
        $this->Cell($w[4],6,number_format($row[4],2),'LR',0,'R');
        $this->Ln();
    }
    //Closure line
    $this->Cell(array_sum($w),0,'','T');
} //function

} // class

$userid = $_SESSION['userid'];
$rechnungsjahr = $_SESSION['rechnungsjahr'];

$ctrl = array(
	'Januar' => '01',
	'Februar' => '02',
	'März' => '03',
	'April' => '04',
	'Mai' => '05',
	'Juni' => '06',
	'Juli' => '07',
	'August' => '08',
	'September' => '09',
	'Oktober' => '10',
	'November' => '11',
	'Dezember' => '12'
);
// true = Typ-Vergleich von in_array, hier nötig (1 != 01);
if(in_array($_GET['rm'], $ctrl, true))
	{
	$rechnungsmonat = $_GET['rm'];
} else {
	$rechnungsmonat = date('m');
}

$sql = "select preis as 'EUR/Std.',
	DATE_FORMAT(begintime, '%d.%m.%Y %H:%i')as Beginn,
	DATE_FORMAT(endtime, '%d.%m.%Y %H:%i')as Ende,
	TIMEDIFF(endtime,begintime) as Dauer,
	TIME_TO_SEC(TIMEDIFF(endtime,begintime))*preis/3600 as Betrag
	from studio_buchung
	where DATE_FORMAT(begintime, '%Y-%m') ='$rechnungsjahr-$rechnungsmonat'
	AND userID = '$userid'
	order by begintime";
$caption = "Buchungen $rechnungsmonat/$rechnungsjahr";

// print $sql;
$sql = mysql_query($sql);
// 1 Feld für summe
$colspan = mysql_num_fields($sql) - 1;
for ($i = 0; $i < mysql_num_fields($sql); $i++)
	{
	$header[$i] = mysql_field_name($sql,$i);
}
$data = array();
$i = 0;
while ($liste1 = mysql_fetch_array($sql, MYSQL_ASSOC))
	{
	foreach ($liste1 as $schluessel => $wert)
		{
		$data[$i][] = $wert;
	}
	$summe += $liste1['Betrag'];
	$i++;
}
// $i läuft weiter
for ($k = 0; $k < $colspan; $k++)
	{
	$data[$i][] = '';
}
$data[$i][] = $summe;

$pdf=new PDF();
// var_dump($data);
// var_dump($header);
$pdf->SetFont('Arial','',10);
$pdf->AddPage();
$pdf->ImprovedTable($header,$data);
$pdf->Output();

?>
