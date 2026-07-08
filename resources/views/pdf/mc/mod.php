<?php
session_start();
$servername = "localhost";
$username = "";
$password = "";
$bdname = "unimaid";
$conn = mysqli_connect($servername, $username, $password, $bdname);

$sql = "SELECT ID,std_ID, session,ca,exam,grade FROM results ORDER BY std_ID ASC";
$result = mysqli_query($conn, $sql);
require('fpdf/fpdf.php');
$name = "Abubakar Abdullahi";
$id = "15/05/04/033";
$pdf = new FPDF(); 
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

$pdf->SetFont('Arial','',14);
$pdf->Cell(110,10,'Student\'s name: '.$name,0,0,'L');
$pdf->Ln(7);
$pdf->Cell(110,10,'ID. No. '.$id,0,0,'L');
$pdf->Ln(10);


$pdf->SetFont('Arial','B',12);
$pdf->Cell(60);
$width_cell=array(20,30,20,30);
$pdf->SetFillColor(193,229,252); // Background color of header 
// Header starts /// 

$pdf->Cell($width_cell[0],10,'ID',1,0,'C',true); // First header column 
$pdf->Cell($width_cell[1],10,'NAME',1,0,'C',true); // Second header column
$pdf->Cell($width_cell[2],10,'CLASS',1,0,'C',true); // Third header column 
$pdf->Cell($width_cell[3],10,'MARK',1,1,'C',true); // Fourth header column
//// header is over ///////

$pdf->SetFont('Arial','',10);
 

if (mysqli_num_rows($result) > 0) {
  $x=0;
while($row = mysqli_fetch_assoc($result)) { 
  $x=$x+1;
  $sn = $_SESSION["sn"] = $row["ID"];
      $id = $_SESSION["id"] = $row["std_ID"];
      $session = $_SESSION["session"] = $row["session"];
      $ca1 = $_SESSION["ca1"] = $row["ca"];
      $exam = $_SESSION["exam"] = $row["exam"];
      $grade = $_SESSION["grade"] = $row["grade"];




       $pdf->Cell($width_cell[0],10,$id,1,0,'C',false); // First column of row 1 
$pdf->Cell($width_cell[1],10,$session,1,0,'C',false); // Second column of row 1 
$pdf->Cell($width_cell[2],10,$ca1,1,0,'C',false); // Third column of row 1 
$pdf->Cell($width_cell[3],10,$exam."jh ghjhuh",1,1,'L',false); // Fourth column of row 1 
$pdf->Ln(10); 
  } 

$pdf->Output();
} 
?>