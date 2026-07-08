<?php
include('pdf_mc_table.php');
 //include 'fpdf/fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';
 $servername = "localhost";
$username = "";
$password = "";
$bdname = "assalafi";
$conn = mysqli_connect($servername, $username, $password, $bdname);
$name = '';$id_no = '';$state = '';$nationality = '';$sex = '';
$first = 0;$second = 0;$f = 0;$s = 0;$cugp = 0;$a=0;$b=0;$c=0;$d=0;$e=0;$f=0;
//$code = mysqli_real_escape_string($con,$_POST['course']);
//$session = mysqli_real_escape_string($con,$_POST['session']);
$id = "21/05/04/004";
$session = "2020/2021";
$semester = "First";
$code = "CPE201";
$title = "Introduction to Computer Engineering";
$unit = "2";
  $sqll = "SELECT * FROM courses WHERE code='$code'";
            $select = mysqli_query($conn,$sqll);
            $select = mysqli_fetch_assoc($select);
            if(isset($select['code'])){
                $semester = $select['semester'];
                $title = $select['title'];
                $unit = $select['unit'];
            }
$sql = "SELECT * FROM results where session = '2020/2021' ORDER BY course ASC";
$result = mysqli_query($conn, $sql);
 $pdf=new exFPDF();
 $pdf->AddPage(); 
 $pdf->SetFont('helvetica','',10);
 $pdf->AddFont('lato','','Lato-Regular.php');
 $pdf->AddFont('FontUTF8','','Arimo-Regular.php'); 
 $pdf->AddFont('FontUTF8','B','Arimo-Bold.php'); 
 $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php'); 
 $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
 
 $table=new easyTable($pdf, '{11, 40, 13, 13, 14, 10, 10, 10, 10, 10, 10, 10, 11, 18}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
 $table->easyCell('University of Maiduguri', 'colspan:14; font-style:B; font-size:18; align:C;border:0;');
 $table->printRow();
 $table->easyCell('(Faculty of Engineering)', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
 $table->printRow();
 $table->easyCell('Department of Computer Engineering', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
 $table->printRow();
 $pdf->Ln(5);
 $table->easyCell($semester.' Semester End-of-Course Examination Results for '.$session.' Acadamic Session', 'colspan:14; font-size:14; align:C;border:0;');
 $table->printRow();
 $table->easyCell('Course Code/Title    '.$code.': '.$title.'  Units: '.$unit, 'colspan:14; font-size:14; align:C;border:0;');
 $table->printRow();
  $pdf->Ln(1);
$table->rowStyle('align:{CCCCCCCCCCCCCC};');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
 $table->easyCell("S/No.", 'rowspan:2');
 $table->easyCell("ID. No.", 'rowspan:2');
 $table->easyCell("C.A Marks (30%)", 'rowspan:2');
 $table->easyCell("Exam. Marks (70%)", 'rowspan:2');
 $table->easyCell("Total Marks (100%)", 'rowspan:2');
 $table->easyCell("Grades", 'colspan:6','align:C; valign:M');
 $table->easyCell("GP", 'rowspan:2');
 $table->easyCell("U.GP", 'rowspan:2');
 $table->easyCell("Remarks.", 'rowspan:2');
 $table->printRow();

 $table->rowStyle('align:{CCCCCC};');
 $table->easyCell("A");
 $table->easyCell("B");
 $table->easyCell("C");
 $table->easyCell("D");
 $table->easyCell("E");
 $table->easyCell("F");
 $table->printRow();
 if (mysqli_num_rows($result) > 0) {
  $x=0;
  $unit = 0;
  $ugp = 0;
while($row = mysqli_fetch_assoc($result)) { 
  $x=$x+1;
$code = $row['course'];
$unit = $unit + $row['unit'];
$ugp = $ugp + $row['ugp'];
$table->rowStyle('align:{CCCCCCCCCCCCCC};');
$table->easyCell($x);
 $table->easyCell($row['id_no']);
 if($row['ca']<10){$table->easyCell('0'.$row['ca']);}else{$table->easyCell($row['ca']);}
 if($row['exam']<10){$table->easyCell('0'.$row['exam']);}else{$table->easyCell($row['exam']);}
 if($row['total']<10){$table->easyCell('0'.$row['total']);}else{$table->easyCell($row['total']);}
 if($row['grade'] == 'A'){$table->easyCell("A"); $a++;}else{$table->easyCell("");}
 if($row['grade'] == 'B'){$table->easyCell("B"); $b++;}else{$table->easyCell("");}
 if($row['grade'] == 'C'){$table->easyCell("C"); $c++;}else{$table->easyCell("");}
 if($row['grade'] == 'D'){$table->easyCell("D"); $d++;}else{$table->easyCell("");}
 if($row['grade'] == 'E'){$table->easyCell("E"); $e++;}else{$table->easyCell("");}
 if($row['grade'] == 'F'){$table->easyCell("F"); $f++;}else{$table->easyCell("");}
 $table->easyCell($row['gp'].'.0');
 if($row['ugp']<10){$table->easyCell('0'.$row['ugp']);}else{$table->easyCell($row['ugp']);}
 $table->easyCell($row['remark']);
 $table->printRow();


}

$table->rowStyle('align:{CCCCCCCCCCCCCC};');
$table->easyCell("Total", 'colspan:5');
 if($a<10){$table->easyCell('0'.$a);}else{$table->easyCell($a);}
 if($b<10){$table->easyCell('0'.$b);}else{$table->easyCell($b);}
 if($c<10){$table->easyCell('0'.$c);}else{$table->easyCell($c);}
 if($d<10){$table->easyCell('0'.$d);}else{$table->easyCell($d);}
 if($e<10){$table->easyCell('0'.$e);}else{$table->easyCell($e);}
 if($f<10){$table->easyCell('0'.$f);}else{$table->easyCell($f);}
 $table->easyCell($a+$b+$c+$d+$e+$f, 'colspan:3');
 $table->printRow();
}

 $table->endTable(4);
 
//-----------------------------------------

 $pdf->Output(); 

?>