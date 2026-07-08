<?php
use Illuminate\Support\Facades\DB;
include('pdf_mc_table.php');
 //include 'fpdf/fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';
$name = '';$pend = '';$p = 0;
$first = 0;$upper = 0;$lower = 0;$third = 0;$pass = 0;$deferred = 0;$expulsion = 0;$withdraw = 0;$voluntary = 0;$suspension = 0;$others = 0;
$first = 0;$second = 0;$f = 0;$s = 0;$cugp = 0;$a=0;$b=0;$c=0;$d=0;$e=0;$f=0;$class = '';
$session = $session;
$program = $program;
$rec = DB::table('program')->where(['code' => $program])->get();
foreach ($rec as $row) {
    $award = $row -> award;
    $faculty = DB::table('faculty')->where(['code' => $row -> faculty])->value('title');
    $dept = $row -> title;
    $duration = ($row -> duration) * 100;
}
//$duration = 200;
//$sql = "SELECT * FROM results where session = '$session' ORDER BY course ASC";
//$result = mysqli_query($con, $sql);
 $pdf=new exFPDF();
 $pdf->AddPage();
 $pdf->SetFont('helvetica','',8);
 $pdf->AddFont('lato','','Lato-Regular.php');
 $pdf->AddFont('FontUTF8','','Arimo-Regular.php');
 $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
 $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');
 $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
 $table=new easyTable($pdf, '{20, 20, 20, 20, 20, 20, 20}', 'width:170; border-color:black; font-size:8; border:1; paddingY:0.5;');
 $table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
 $table->printRow();
 $table->easyCell(ucwords(strtolower($faculty)), 'colspan:14; font-size:10; align:C;border:0;');
 $table->printRow();
 $table->easyCell('(Department Of '.ucwords(strtolower($dept.')')), 'colspan:14; font-size:10; align:C;border:0;');
 $table->printRow();
 $table->easyCell('C O N F I D E N T I A L', 'colspan:14; font-size:10; align:C;border:0;');
 $table->printRow();
 $table->easyCell('STUDENT DEGREE COMPUTATION RECORD', 'colspan:14; font-size:10; align:C;border:0;');
 $table->printRow();
 $pdf->Ln(4);

$session_history = DB::table('session_history')->where(['session' => $session, 'level' => $duration, 'program' => $program])->get();
foreach ($session_history as $results) {
$id = $results -> username;
$name = DB::table('students')->where(['username' => $id])->select('fullname')->value('fullname');

$sn = 1;
$unit = 0;
$ugp = 0;
$cgpa = 0;
$table->easyCell('ID. NO: '.$id, 'colspan:14; font-size:12; align:L;border:0;');
$table->printRow();
$student=DB::table('session_history')->where(['username' => $results -> username])->orderBy('next_session', 'ASC')->distinct()->get('next_session');
    foreach ($student as $row) {

$table->easyCell('Session '.$row-> next_session, 'colspan:14; font-size:10; align:L;border:0;');
$table->printRow();
$table->rowStyle('align:{CCCCCCC}; font-style:B;');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
$table->easyCell("COURSE");
$table->easyCell("CODE");
$table->easyCell("MARKS");
$table->easyCell("GRADE");
$table->easyCell("GP");
$table->easyCell("UNITS");
$table->easyCell("PROD");
$table->printRow();

$cunit = 0;
$cugp = 0;
$ccgpa = 0;
    $regs = DB::table('student_course_registration')->where(['username' => $id])->orderBy('username', 'ASC')->get();
    foreach ($regs as $reg) {
        $course = substr($reg -> code, 0, -3);
        $code = substr($reg -> code, -3);
        $unit = $unit + $reg -> unit;
        $ugp = $ugp + $reg -> ugp;

        $cunit = $cunit + $reg -> unit;
        $cugp = $cugp + $reg -> ugp;
        $table->rowStyle('align:{CCCCCCC};');
        $table->easyCell($course);
        $table->easyCell($code);
        $table->easyCell($reg -> total);
        $table->easyCell($reg -> grade);
        $table->easyCell($reg -> point);
        $table->easyCell($reg -> unit);
        $table->easyCell($reg -> ugp);
        $table->printRow();
    }

    $pdf->Ln(2);
    $ccgpa = $cugp/$cunit;
    $cgpa = $ugp/$unit;
    $table->rowStyle('align:{LCCCCCC};');
    $table->easyCell("TOTAL",'font-style:B;');
    $table->easyCell("",'colspan:4;');
    $table->easyCell($cunit);
    $table->easyCell($cugp);
    $table->printRow();
    $table->rowStyle('align:{LCCCCCC};');
    $table->easyCell("CUM",'font-style:B;');
    $table->easyCell("BF",'font-style:B; colspan:4;');
    $table->easyCell($unit);
    $table->easyCell($ugp);
    $table->printRow();
    $table->rowStyle('align:{LCCCCCC};');
    $table->easyCell("CUM",'font-style:B;');
    $table->easyCell("CF",'font-style:B; colspan:4;');
    $table->easyCell($cunit);
    $table->easyCell($cugp);
    $table->printRow();
    $table->rowStyle('align:{LCCCCCC};');
    $table->easyCell("GPA",'font-style:B;');
    $table->easyCell("",'colspan:5;');
    $table->easyCell(number_format((float)$ccgpa,2,'.',''),'font-style:B;');
    $table->printRow();
    $table->rowStyle('align:{LCCCCCC};');
    $table->easyCell("CGPA",'font-style:B;');
    $table->easyCell("",'colspan:5;');
    $table->easyCell(number_format((float)$cgpa,2,'.',''),'font-style:B;');
    $table->printRow();
    $cunit = 0;
    $cugp = 0;
    $ccgpa = 0;
  }

  $unit = 0;
  $ugp = 0;
  $cgpa = 0;
  $pdf->AddPage();
}

 $table->endTable(4);

//-----------------------------------------
 //$name = $lvl.'L '.$session;
 //$pdf->Output('I',$name.'.pdf');
 $pdf->Output();
 die;

?>
