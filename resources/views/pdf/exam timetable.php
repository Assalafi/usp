<?php
use Illuminate\Support\Facades\DB;
include('pdf_mc_table.php');
 //include 'fpdf/fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';

 $pdf=new exFPDF();
 $pdf->AddPage('O');
 $pdf->SetFont('helvetica','',10);
 $pdf->AddFont('lato','','Lato-Regular.php');
 $pdf->AddFont('FontUTF8','','Arimo-Regular.php');
 $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
 $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');
 $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
//$pdf->MultiCell(190, '10', $final, '', 'L');
 
 $table=new easyTable($pdf, '{45, 45, 45, 45, 45, 45}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
 $table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
 $table->printRow();
 $table->easyCell('('.DB::table('faculty')->where('code', $faculty)->value('title').' EXAM TIMETABLE)', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
 $table->printRow();
 $pdf->Ln(5);
 $table->rowStyle('align:{CCCCCC};');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
 $table->easyCell("Date",'font-style:B;');
 $table->easyCell("Starting Time",'font-style:B;');
 $table->easyCell("Ending Time",'font-style:B;');
 $table->easyCell("Course",'font-style:B;');
 $table->easyCell("Hall",'font-style:B;');
 $table->easyCell("Lecturer",'font-style:B;');
 $table->printRow();

 $data = DB::table('exam_timetable')->where(['faculty' => $faculty])->orderBy('date','ASC')->orderBy('start','ASC')->get();
 $x = 0;
 $mon = 0;$tue = 0;$wed = 0;$thu = 0;$fri = 0;$sat = 0;
 $monday = $tuesday = $wednesday = $thursday = $friday = $saturday = array();
 foreach ($data as $row) {
    $x++;
    //$info = $row -> course."\n".date('h:i A', strtotime($row -> start))."\n".date('h:i A', strtotime($row -> end))."\n".$row -> hall."\n".$row -> lecturer;
    $table->easyCell(date('l', strtotime($row -> date))."\n".$row -> date);
    $table->easyCell(date('h:i A', strtotime($row -> start)));
    $table->easyCell(date('h:i A', strtotime($row -> end)));
    $table->easyCell($row -> course);
    $table->easyCell($row -> hall);
    $table->easyCell($row -> lecturer);
    $table->printRow();

 }
 $table->endTable();
 echo $pdf->Output('exam timetable','I');
exit;

?>