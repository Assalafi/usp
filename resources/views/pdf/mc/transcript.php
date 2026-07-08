<?php
include('pdf_mc_table.php');
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
$pdf->Cell(110,10,'Student\'s name: '.$name,0,0,'L');
$pdf->Cell(30);
$pdf->Cell(110,10,'Sex: '.$sex,0,0,'L');
$pdf->Ln(6);
$pdf->Cell(110,10,'ID. No. '.$id,0,0,'L');
$pdf->Cell(30);
$pdf->Cell(110,10,'State: '.$state,0,0,'L');
$pdf->Ln(6);
$pdf->Cell(140);
$pdf->Cell(20,10,'Nationality: '.$nationality,0,0,'L');
$pdf->Ln(10);
$pdf -> Line(10, 50, 195, 50);
$pdf->SetFont('Arial','',8);
$pdf->Cell(20,10,'Part 1 First Semester',0,0,'L');
$pdf->Cell(60);
$pdf->Cell(20,10,'2020/2021 Acadamic Session',0,0,'L');
$pdf->Ln(7);
//set width for each column (6 columns)
$pdf->SetWidths(Array(35,74,10,14,21,17,15));
$pdf -> SetAligns(Array('L','L','C','C','C','C','C'));
//set line height. This is the height of each lines, not rows.
$pdf->SetLineHeight(5);
$pdf->Row(Array(
		"Course Code",
		"Course Title",
		"Unit",
		"Marks",
		"Grade Point(GP)",
		"Product (UGP)",
		"Remark"
	));
$pdf->SetFont('Arial','',8);
if (mysqli_num_rows($result) > 0) {
  $x=0;
  $unit = 0;
  $ugp = 0;
while($row = mysqli_fetch_assoc($result)) { 
  $x=$x+1;
$code = $row['course'];
$unit = $unit + $row['unit'];
$ugp = $ugp + $row['ugp'];
$sql = "SELECT * FROM courses WHERE code = '$code'";
$select = mysqli_query($conn,$sql);
$select = mysqli_fetch_assoc($select);
$pdf->Row(Array(
		$select['code'],
		$select['title'],
		$select['unit'],
		$row['total'],
		$row['gp'],
		$row['ugp'],
		$row['remark']
	));
}
$pdf->Row(Array(
		"",
		"Total",
		$first = $unit,
		"",
		"",
		$f = $ugp,
		""
	));
}
$sql = "SELECT * FROM results where id_no = '21/05/04/004' and session = '2020/2021' and semester = 'Second' ORDER BY course ASC";
$result = mysqli_query($conn, $sql);
$pdf->Cell(20,10,'Part 1 Second Semester',0,0,'L');
$pdf->Cell(60);
$pdf->Cell(20,10,'2020/2021 Acadamic Session',0,0,'L');
$pdf->Ln(7);
//set width for each column (6 columns)
$pdf->SetWidths(Array(35,74,10,14,21,17,15));
//set line height. This is the height of each lines, not rows.
$pdf->SetLineHeight(6);
$pdf->Row(Array(
		"Course Code",
		"Course Title",
		"Unit",
		"Marks",
		"Grade Point(GP)",
		"Product (UGP)",
		"Remark"
	));

//reset font
$pdf->SetFont('Arial','',8);

if (mysqli_num_rows($result) > 0) {
  $x=0;
  $unit = 0;
  $ugp = 0;
while($row = mysqli_fetch_assoc($result)) { 
  $x=$x+1;
$code = $row['course'];
$unit = $unit + $row['unit'];
$ugp = $ugp + $row['ugp'];
$sql = "SELECT * FROM courses WHERE code = '$code'";
$select = mysqli_query($conn,$sql);
$select = mysqli_fetch_assoc($select);
$pdf->Row(Array(
		$select['code'],
		$select['title'],
		$select['unit'],
		$row['total'],
		$row['gp'],
		$row['ugp'],
		$row['remark']
	));
}
$pdf->Row(Array(
		"",
		"Total",
		$second = $unit,
		"",
		"",
		$s = $ugp,
		""
	));
}
$pdf -> ln(6);
$pdf->SetWidths(Array(185));
$pdf->SetLineHeight(5);
$pdf->Row(Array(
		"SUMMARY OF RESULT:"."\nCommulative units:                                                              ".$unit = $first+$second."\nCommulative Products:                                                       ".$ugp = $f+$s." \nCommulative Grade Point Average(CGPA):                       ".round($cgpa = $ugp/$unit,2)
	));
$pdf -> ln();
$pdf->SetFont('Arial','',10);
$pdf->Cell(110,13.5,'LEVEL COODINATOR SIGNATURE & DATE .............................................................................................',0,0,'L');
$pdf -> ln();
$pdf->Cell(110,1,'HEAD OF DEPARTMENT SIGNATURE & DATE ........................................................................................',0,0,'L');
//$pdf->Output('D',$name.'.pdf');
$pdf->Output();
?>