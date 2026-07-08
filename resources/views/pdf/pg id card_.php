<?php
    require('fpdf/fpdf.php');
    use Illuminate\Support\Facades\DB;
    use App\Models\Student;
    use Illuminate\Database\QueryException;
    use Endroid\QrCode\QrCode;
    use Endroid\QrCode\Writer\PngWriter;

    if(isset($id)){
        $data = Student::where('id', $id)->select('fullname', 'username', 'jamb_no', 'faculty', 'program', 'state_origin', 'country', 'kin_name', 'kin_phone', 'picture', 'signiture','passport_pic', 'passport_sign', 'issue_date', 'expire_date')->get();
    }

    $pdf=new FPDF();
    foreach ($data as $row) {
        $name = $row->fullname;
        if($row->username){
            $id = $row->username;
        }else{
            $id = $row->jamb_no;
        }
        $faculty = $row->faculty;
        $course = $row->program;
        $state = $row->state_origin;
        $nationality = $row->country;
        $kin_name = $row->kin_name;
        $kin_phone = $row->kin_phone;
        if($row->passport_pic){
            $image = 'storage/passport_pic/'.$row->passport_pic;
            $path_parts = pathinfo($image);
            $ext = strtoupper($path_parts['extension']);
        }else{
            $image = 'card/default.jpg';
            $ext = strtoupper('JPG');
        }

        if($row->passport_sign){
            $signature = 'storage/passport_sign/'.$row->passport_sign;
            $path_parts = pathinfo($signature);
            $exts = strtoupper($path_parts['extension']);
        }else{
            $signature = 'card/student sign.png';
            $exts = strtoupper('PNG');
        }

    $pdf->AddPage();
    $pdf->SetFont('times','',10);

    $pdf -> Image('card/pg-front.jpeg',75,25,86,54,'JPEG');
    $pdf -> Image('card/pg-back.jpeg',75,90,86,54,'JPEG');
    //$pdf -> Image($image,228,59,43,47,$ext);
    try{
        $pdf -> Image($image,77,42,18.8,20,$ext);
    }catch (\Exception $e) {
        $pdf -> Image('card/default.jpg',77,42,18.8,20,'JPG');
    } finally {}

    try{
        $pdf -> Image($signature,85,125,10,5,$exts);
    }catch (\Exception $e) {
        $pdf -> Image('card/student sign.png',85,125,10,5,'PNG');
    } finally {}

    $pdf -> Image('card/registrar sign.png',119,125,20,10,'PNG');

    // $pdf->SetY(23);
    // $pdf->SetX(110);
    // // white color
    // $pdf->SetFont('Arial', 'B', 7);
    // $pdf->Cell(54, 10, 'PG STUDENT', 0, 1, 'L');

    // black color
    //$pdf->SetTextColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetY(66.5);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(54, 10, $name, 0, 1, 'L');

    $pdf->SetY(70);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(54, 10, $id, 0, 1, 'L');

    // White color
    $pdf->SetTextColor(255, 255, 255);

    $pdf->SetY(46);
    $pdf->SetX(99);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, 'Faculty: '.DB::table('faculty')->where('code', $faculty)->value('title'), 0, 1, 'L');

    $pdf->SetY(50);
    $pdf->SetX(99);
    $pdf->SetFont('Arial', 'B', 5);
    $pdf->Cell(54, 10, 'Course: '.DB::table('program')->where('code', $course)->value('award').' '.DB::table('program')->where('code', $course)->value('title'), 0, 1, 'L');

    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetY(57);
    $pdf->SetX(99);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $state ? ' State: '.$state : 'State: N/A', 0, 1, 'L');

    $pdf->SetY(61);
    $pdf->SetX(99);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $nationality ? 'Nationality: '.$nationality : 'Nationality: N/A', 0, 1, 'L');

    $pdf->SetY(131.6);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $row->issue_date ? date('d/m/Y', strtotime($row->issue_date)) : 'N/A', 0, 1, 'C');

    $pdf->SetY(136.5);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $row->expire_date ? date('d/m/Y', strtotime($row->expire_date)) : 'N/A', 0, 1, 'C');

    $pdf->SetY(114.5);
    $pdf->SetX(110);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $kin_name ?? 'N/A', 0, 1, 'L');

    $pdf->SetY(118);
    $pdf->SetX(110);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $kin_phone ?? 'N/A', 0, 1, 'L');

    }

    $pdf->Output();
    exit;
?>
