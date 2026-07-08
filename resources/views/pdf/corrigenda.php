<?php
use Illuminate\Support\Facades\DB;
include('pdf_mc_table.php');
 //include 'fpdf/fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';

 class PDFWithWatermark extends exFPDF {
     protected $angle = 0;

     function Header() {
         // Add watermark on every page
         $this->SetFont('Arial', 'B', 80);
         $this->SetTextColor(255, 192, 203); // Pink color for the watermark

         // Calculate the center position
         $textWidth = $this->GetStringWidth('NOT APPROVED');
         $x = ($this->GetPageWidth() - $textWidth) / 2;
         $y = ($this->GetPageHeight() / 2);

         // Rotate and print the watermark
         $this->RotateText($x, $y, 'NOT APPROVED', 45);

         // Reset the rotation back to 0
         $this->Rotate(0);
     }

     function RotateText($x, $y, $txt, $angle) {
         // Rotate around a point
         $this->Rotate($angle, $x, $y);
         $this->Text(-20, 210, $txt);
     }

     function Rotate($angle, $x = -1, $y = -1) {
         if ($x == -1) {
             $x = $this->x;
         }
         if ($y == -1) {
             $y = $this->y;
         }
         if ($this->angle != 0) {
             $this->_out('Q');
         }
         $this->angle = $angle;
         if ($angle != 0) {
             $angle *= M_PI / 180;
             $c = cos($angle);
             $s = sin($angle);
             $cx = $x * $this->k;
             $cy = ($this->h - $y) * $this->k;
             $this->_out(sprintf('q %.2F %.2F %.2F %.2F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
         }
     }

     function _endpage() {
         if ($this->angle != 0) {
             $this->angle = 0;
             $this->_out('Q');
         }
         parent::_endpage();
     }
 }
$a=0;$b=0;$c=0;$d=0;$e=0;$f=0;$name='';
$session = $session;
$department = DB::table('department')->where(['code' => $dept])->value('title');
$faculty = DB::table('department')->where(['code' => $dept])->value('faculty');
$faculty = DB::table('faculty')->where(['code' => $faculty])->value('title');

$data = DB::table('corrigenda')
->select(
    'results.code AS code',
    'results.unit AS unit',
)
->leftJoin('results', function ($join) {
    $join->on('results.id', '=', 'corrigenda.result_id');
})
->groupBy('results.code', 'results.unit')
->where('corrigenda.department', $dept)
->where('corrigenda.session', $session)
->get();
$courses = '';
$no = count($data);
$flag = 0;
foreach($data as $row){
    $flag++;
    if($no == 1){
        $courses .= $row -> code . ' (' . $row -> unit . ' Units)';
    }else if($no == 2){
        if($flag == 2){
            $courses = $courses . ' and ' . $row -> code . ' (' . $row -> unit . ' Units)';
        }else{
            $courses .= $row -> code . ' (' . $row -> unit . ' Units)';
        }
    }else{
        if($flag == $no){
            $courses = $courses . ' and ' . $row -> code . ' (' . $row -> unit . ' Units)';
        }else{
            $courses .= $row -> code . ' (' . $row -> unit . ' Units), ';
        }
    }
}
$users = DB::table('corrigenda')
->select(
    'results.username AS username'
)
->leftJoin('results', function ($join) {
    $join->on('results.id', '=', 'corrigenda.result_id');
})
->where('corrigenda.department', $dept)
->where('corrigenda.session', $session)
->get();
$user = '';
$no = count($users);
$flag = 0;
foreach($users as $row){
    $flag++;
    if($no == 1){
        $user .= $row -> username;
    }else if($no == 2){
        if($flag == 2){
            $user = $user . ' and ' . $row -> username;
        }else{
            $user .= $row -> username;
        }
    }else{
        if($flag == $no){
            $user = $user . ' and ' . $row -> username;
        }else{
            $user .= $row -> username . ', ';
        }
    }
}
$pdf= new PDFWithWatermark();
$pdf->AddPage('O');
$pdf->SetFont('helvetica','',10);
$pdf->AddFont('lato','','Lato-Regular.php');
$pdf->AddFont('FontUTF8','','Arimo-Regular.php');
$pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
$pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8','I','Arimo-Italic.php');

//$pdf->Rotate(0); // Reset rotation

$table=new easyTable($pdf, '{11, 40, 20, 20, 20, 25, 25, 20, 20, 15, 15, 20, 30, 30}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
 $table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
 $table->printRow();
 $table->easyCell('University of Maiduguri', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
 $table->printRow();
 $table->easyCell('('.ucwords(strtolower($faculty)).')', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
 $table->printRow();
 $table->easyCell('Department of '.ucwords(strtolower($department)), 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
 $table->printRow();
 $pdf->Ln(5);

 $table->easyCell('Correction of Examination Results', 'colspan:7; font-size:12; align:L;border:0;font-style:B;');
 $table->easyCell(session('system_session').' Academic Session', 'colspan:7; font-size:12; align:R;border:0;font-style:B;');
 $table->printRow();
  $pdf->Ln(2);
  $table->easyCell('During the Presentation of results of '.$courses.', at the 290th Senate Meeting held on '.date('D d M, Y').', the results of candidate(s) with ID. No. '.$user.' was/were wrongly presented. The wrong and correct result(s) are presented below.', 'colspan:14; font-size:12; align:L;border:0;');
  $table->printRow();
   $pdf->Ln(2);
$table->rowStyle('align:{CCCCCCCCCCCCCC};');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
 $table->easyCell("S/No.");
 $table->easyCell("ID. No.");
 $table->easyCell("Course Code");
 $table->easyCell("Semester");
 $table->easyCell("Units");
 $table->easyCell("C.A. Marks");
 $table->easyCell("Exam Marks");
 $table->easyCell("Total Marks");
 $table->easyCell("Grade");
 $table->easyCell("GP");
 $table->easyCell("U.GP");
 $table->easyCell("Remarks");
 $table->easyCell("Presentation");
 $table->easyCell("Dept");
 $table->printRow();
  $x=0;

  $data = DB::table('corrigenda')
  ->select(
      'corrigenda.*',
      'results.username AS username',
      'results.code AS code',
      'results.unit AS unit',
      'results.ca AS old_ca',
      'results.exam AS old_exam',
      'results.total AS old_total',
      'results.grade AS old_grade',
      'results.point AS old_point',
      'results.ugp AS old_ugp',
      'results.remark AS old_remark'
  )
  ->leftJoin('results', function ($join) {
      $join->on('results.id', '=', 'corrigenda.result_id');
  })
  ->where('corrigenda.department', $dept)
  ->where('corrigenda.session', $session)
  ->get();
foreach ($data as $row) {
    $x=$x+1;
    $table->rowStyle('align:{CCCCCCCCCCCCCC};');
    $table->easyCell($x, 'rowspan:2');
    $table->easyCell($row -> username, 'rowspan:2');
    $table->easyCell($row -> code, 'rowspan:2');
    $table->easyCell($row -> semester, 'rowspan:2');
    $table->easyCell($row -> unit, 'rowspan:2');
    $table->easyCell($row -> old_ca);
    $table->easyCell($row -> old_exam);
    $table->easyCell($row -> old_total);
    $table->easyCell($row -> old_grade);
    $table->easyCell($row -> old_point);
    $table->easyCell($row -> old_ugp);
    $table->easyCell($row -> old_remark);
    $table->easyCell('Wrong Result');
    $table->easyCell($department, 'rowspan:2');
    $table->printRow();

    $table->rowStyle('align:{CCCCCCCCCCCCCC};');
    $table->easyCell($row -> ca);
    $table->easyCell($row -> exam);
    $table->easyCell($row -> total);
    $table->easyCell($row -> grade);
    $table->easyCell($row -> point);
    $table->easyCell($row -> ugp);
    $table->easyCell($row -> remark);
    $table->easyCell('Correct Result');
    $table->printRow();
}
 $table->endTable(4);
 $pdf->Output();
 die;
?>
