<?php
include('pdf_mc_table.php');
session_start();
$servername = "localhost";
$username = "";
$password = "";
$bdname = "unimaid";
$conn = mysqli_connect($servername, $username, $password, $bdname);
$sql = "SELECT ID,std_ID, session,ca,exam,grade FROM results ORDER BY std_ID ASC";
$result = mysqli_query($conn, $sql);
//make new object
$pdf = new PDF_MC_Table();
//add page, set font
$pdf->AddPage();

	$pdf->SetFont('Arial','B',20);
    $pdf->Cell(60);
    $pdf->Cell(70,10,'University of Maiduguri',0,0,'C');
    $pdf->Ln(7);
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(60);
    $pdf->Cell(70,10,'(Faculty of Engineering)',0,0,'C');
    $pdf->Ln(7);
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(60);
    $pdf->Cell(70,10,'Department of Computer Engineering',0,0,'C');
    $pdf->Ln(10);

$pdf->Cell(20);
$pdf->SetFont('Arial','',14);
//set width for each column (6 columns)
$pdf->SetWidths(Array(10,20,40,40,50));
//set line height. This is the height of each lines, not rows.
$pdf->SetLineHeight(6);
//add table heading using standard cells
//set font to bold
$pdf->SetFont('Arial','B',14);
$pdf->Cell(10,5,"S/N",1,0);
$pdf->Cell(20,5,"ID",1,0);
$pdf->Cell(40,5,"First Name",1,0);
$pdf->Cell(40,5,"Last Name",1,0);
$pdf->Cell(50,5,"Email",1,0);
$pdf->Ln();
//reset font
$pdf->SetFont('Arial','',12);

if (mysqli_num_rows($result) > 0) {
  $x=0;
while($row = mysqli_fetch_assoc($result)) { 
  $x=$x+1;
$pdf->Cell(20);
$pdf->Row(Array(
		$x,
		$row['std_ID'],
		$row['session'],
		$row['ca'],
		$row['exam']
	));
} 
$pdf->Output();} ?>