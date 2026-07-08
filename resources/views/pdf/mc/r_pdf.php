<?php
include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';
session_start();
$servername = "localhost";
$username = "";
$password = "";
$bdname = "assalafi";
$conn = mysqli_connect($servername, $username, $password, $bdname);
$sql = "SELECT * FROM results where id_no = '21/05/04/004' and session = '2020/2021' and semester = 'First' ORDER BY course ASC";
$name = '';$id_no = '';$state = '';$nationality = '';$sex = '';
$first = 0;$second = 0;$f = 0;$s = 0;$cugp = 0;

//if (isset($_POST['load'])) {
//  $id_no = mysqli_real_escape_string($con,$_POST['course']);
  $sqll = "SELECT * FROM students WHERE id_no='21/05/04/004'";
            $select = mysqli_query($conn,$sqll);
            $select = mysqli_fetch_assoc($select);
            if(isset($select['id_no'])){
                $name = $select['name'];
                $nationality = $select['nationality'];
                $sex = $select['gender'];
                $state = $select['state'];
//			}
            }
$result = mysqli_query($conn, $sql);
//$name = "Abubakar Abdullahi";
$id = "21/05/04/004";
$session = "2020/2021";
$semester = "First";
$code = "CPE201";
$title = "Introduction to Computer Engineering";
$unit = "2";
//make new object
$pdf = new PDF_MC_Table();
//add page, set font
$pdf->AddPage();

	$pdf->SetFont('Arial','B',18);
    $pdf->Cell(60);
    $pdf->Cell(70,10,'University of Maiduguri',0,0,'C');
    $pdf->Ln(6);
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(60);
    $pdf->Cell(70,10,'(Faculty of Engineering)',0,0,'C');
    $pdf->Ln(6);
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(60);
    $pdf->Cell(70,10,'Department of Computer Engineering',0,0,'C');
    $pdf->Ln(8);

$pdf->SetFont('Arial','',12);
$pdf->Cell(60);
$pdf->Cell(70,10,$semester.' Semester End-of-Course Examination Results for '.$session.'Acadamic Session',0,0,'C');
$pdf->Ln(7);
$pdf->Cell(60);
$pdf->Cell(70,10,'Course Code/Title    '.$code.': '.$title.'  Units: '.$unit,0,0,'C');
$pdf->Ln(10);
//set width for each column (6 columns)
$pdf1=new exFPDF();
 $pdf1->AddPage(); 
 $pdf1->SetFont('helvetica','',10);
 $pdf1->AddFont('lato','','Lato-Regular.php');
 $pdf1->AddFont('FontUTF8','','Arimo-Regular.php'); 
 $pdf1->AddFont('FontUTF8','B','Arimo-Bold.php'); 
 $pdf1->AddFont('FontUTF8','BI','Arimo-BoldItalic.php'); 
 $pdf1->AddFont('FontUTF8','I','Arimo-Italic.php'); 
 
 $table=new easyTable($pdf1, '{11, 40, 13, 13, 14, 10, 10, 10, 10, 10, 10, 10, 11, 18}', 'width:170; border-color:black; font-size:10; border:1; paddingY:2;');
 $table->easyCell("S/No.", 'rowspan:2');
 $table->easyCell("ID. No.", 'rowspan:2');
 $table->easyCell("C.A Marks (30%)", 'rowspan:2');
 $table->easyCell("Exam. Marks (70%)", 'rowspan:2');
 $table->easyCell("Total Marks (100%)", 'rowspan:2');
 $table->easyCell("Grades", 'colspan:6');
 $table->easyCell("GP", 'rowspan:2');
 $table->easyCell("U.GP", 'rowspan:2');
 $table->easyCell("Remarks.", 'rowspan:2');
 $table->printRow();

 //$table->rowStyle('align:{RCC}; bgcolor:#00ace6;font-style:B');
 $table->easyCell("A");
 $table->easyCell("B");
 $table->easyCell("C");
 $table->easyCell("D");
 $table->easyCell("E");
 $table->easyCell("F");
 $table->printRow(true);

 $table->endTable(4);

//$pdf->Output('D',$name.'.pdf');
//$pdf->Output();
$pdf1->Output();
?>