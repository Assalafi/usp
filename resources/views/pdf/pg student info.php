<?php

use Illuminate\Support\Facades\DB;
    use App\Models\Student;
  include('pdf_mc_table.php');
  //include 'fpdf/fpdf.php';
  include 'exfpdf.php';
  include 'easyTable.php';
  $hall = 'Nil';
  $block = 'Nil';
  $room = 'Nil';
  $bed = 'Nil';
    $data = Student::where('id', $id)->get();
    foreach ($data as $row) {

        if($row->passport_pic){
            $image = 'storage/passport_pic/'.$row->passport_pic;
            $path_parts = pathinfo($image);
            $ext = strtoupper($path_parts['extension']);
        }else{
            $image = 'card/default.jpg';
            $ext = strtoupper('JPG');
        }

    }
  foreach (DB::table('hostel')->where('occupant', $row -> username)->select('hall', 'block', 'room', 'bed')->get() as $hostel) {
    $hall = $hostel -> hall;
    $block = $hostel -> block;
    $room = $hostel -> room;
    $bed = $hostel -> bed;
  }
class PDF extends exFPDF {
    public function Header() {
        $this->Ln(15);
        $this->SetFont('Arial', 'B', 24);
        $this->Cell(0, 10, 'University of Maiduguri', 0, 1, 'C');
        $this->Ln(10);
    }

    public function Section($title, $data, $size) {
        $table = new easyTable($this, '{30, 60,5, 30, 60}', 'width:180; border-color:black; font-size:10; border:1; paddingY:1.5;');
        $table->easyCell('', 'width:10; border:none;');
        $table->easyCell('', 'width:30; border:none;');
        $table->easyCell('', 'width:10; border:none;');
        $table->easyCell('', 'width:30; border:none;');
        $table->printRow();
                $table->easyCell(strtoupper($title), 'colspan:5;border:none;font-style:B; font-size:12; align:C;');
                $table->printRow();
                $i = 1;$x = 0;
        foreach ($data as $label => $value) {
            $x++;
            if($i == 1){
                $table->easyCell(strtoupper($label), 'border:none; border:B;');
                $table->easyCell($value, 'border:none; border:B;');
                $table->easyCell('', 'border:none;');

            }else{
                $table->easyCell(strtoupper($label), 'border:none; border:B;');
                $table->easyCell($value, 'border:none; border:B;');
                $table->printRow();
                $i = 0;
            }
            if($x == $size){
                $table->printRow();
            }
            $i++;


        }

        $table->endTable(10);
    }
}

$pdf = new PDF();
$pdf->AddPage();
  $pdf->SetFont('helvetica','',10);
  $pdf->AddFont('lato','','Lato-Regular.php');
  $pdf->AddFont('FontUTF8','','Arimo-Regular.php');
  $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
  $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');
  $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');

try{
    $pdf -> Image($image,160,5,20,20,$ext);
}catch (\Exception $e) {
    $pdf -> Image('card/default.jpg',160,5,20,20,'JPG');
} finally {}

    $pdf -> Image('uploads/logo.png',30,5,20,20,'PNG');

// Sample data for demonstration
$rows = [
    'Personal Details' => [
        'Name' => $row->first_name .' '. $row->last_name .' '. $row->other_name,
        'ID Number' => $row->username,
        'Jamb NO' => $row->jamb_no,
        'Phone' => $row->contact_phone,
        'Email' => $row->contact_email,
        'Gender' => $row->gender,
        'Marital Status' => $row->mairital_status,
        'Date Of Birth' => $row->date_of_birth,
        'Religion' => $row->religion,
        'Blood group' => $row->blood_group,
    ],
    'Acadamic Details' => [
        'Course' => DB::table('program')->where('code', $row->program)->value('title'),
        'Department' => DB::table('department')->where('code', $row->department)->value('title'),
        'faculty' => DB::table('faculty')->where('code', $row->faculty)->value('title'),
        'Current Level' => strtoupper(DB::table('program')->where('code', $row->program)->value('award')),
        'Mode Of Entry' => strtoupper('Postgraduate'),
    ],
    'Current Address' => [
        'Email' => $row->contact_email,
        'Contact' => $row->contact_phone,
        'Address' => $row->contact_address,
    ],
    'Permanent Address' => [
        'Email' => $row->home_email,
        'Contact' => $row->home_phone,
        'Address' => $row->home_address,
    ],
    'Hostel Details' => [
        'Hall' => $hall,
        'Block' => $block,
        'Room' => $room,
        'Bed' => $bed,
    ],
];

foreach ($rows as $title => $data) {
    $pdf->Section($title, $data, count($data));
}

$pdf->Output();

    exit;
?>
