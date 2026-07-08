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

    // Create custom-sized PDF with no extra space - width and height limited to card + margins
    $pdf = new FPDF('P', 'mm', array(106, 110));

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

        // Add page for front of card
        $pdf->AddPage();
        $pdf->SetFont('times','',10);

        // Front of card - positioned at (0,0)
        $pdf -> Image('card/pg-front.jpeg',0,0,86,54,'JPEG');

        // Student photo
        try{
            $pdf -> Image($image,2,17,18.8,20,$ext);
        }catch (\Exception $e) {
            $pdf -> Image('card/default.jpg',2,17,18.8,20,'JPG');
        } finally {}

        // Name and ID - black color
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY(41.5);
        $pdf->SetX(0);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(54, 10, $name, 0, 1, 'L');

        $pdf->SetY(45);
        $pdf->SetX(0);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(54, 10, $id, 0, 1, 'L');

        // Faculty and course - white color
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetY(21);
        $pdf->SetX(24);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(54, 10, 'Faculty: '.DB::table('faculty')->where('code', $faculty)->value('title'), 0, 1, 'L');

        $pdf->SetY(25);
        $pdf->SetX(24);
        $pdf->SetFont('Arial', 'B', 5);
        // Limit course display length if needed
        $courseTitle = DB::table('program')->where('code', $course)->value('title');
        $courseAward = DB::table('program')->where('code', $course)->value('award');
        $courseInfo = 'Course: '.$courseAward.' '.$courseTitle;
        if(strlen($courseInfo) > 50) {
            $courseInfo = substr($courseInfo, 0, 47).'...';
        }
        $pdf->Cell(54, 10, $courseInfo, 0, 1, 'L');

        // State and nationality - black color
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY(32);
        $pdf->SetX(24);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(54, 10, $state ? ' State: '.$state : 'State: N/A', 0, 1, 'L');

        $pdf->SetY(36);
        $pdf->SetX(24);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(54, 10, $nationality ? 'Nationality: '.$nationality : 'Nationality: N/A', 0, 1, 'L');

        // Add page for back of card
        $pdf->AddPage();
        $pdf->SetFont('times','',10);

        // Back of card - positioned at (0,0)
        $pdf -> Image('card/pg-back.jpeg',0,0,86,54,'JPEG');

        // Student signature
        try{
            $pdf -> Image($signature,10,35,10,5,$exts);
        }catch (\Exception $e) {
            $pdf -> Image('card/student sign.png',10,35,10,5,'PNG');
        } finally {}

        // Registrar signature
        $pdf -> Image('card/registrar sign.png',44,35,20,10,'PNG');

        // Issue and expiry dates
        $pdf->SetY(41.6);
        $pdf->SetX(0);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(86, 5, $row->issue_date ? date('d/m/Y', strtotime($row->issue_date)) : 'N/A', 0, 1, 'C');

        $pdf->SetY(46.5);
        $pdf->SetX(0);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(86, 5, $row->expire_date ? date('d/m/Y', strtotime($row->expire_date)) : 'N/A', 0, 1, 'C');

        // Next of kin details
        $pdf->SetY(24.5);
        $pdf->SetX(35);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(54, 10, $kin_name ?? 'N/A', 0, 1, 'L');

        $pdf->SetY(28);
        $pdf->SetX(35);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(54, 10, $kin_phone ?? 'N/A', 0, 1, 'L');
    }

    $pdf->Output();
    exit;
?>
