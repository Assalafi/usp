<?php
require('fpdf/fpdf.php');

use Illuminate\Support\Facades\DB;

// Initialize PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Times', 'B', 14);

// Add Title
$pdf->Cell(0, 10, 'UNIVERSITY OF MAIDUGURI', 0, 1, 'C');
$pdf->Cell(0, 10, '369th Submission', 0, 1, 'C');
$pdf->SetFont('Times', '', 12);
$pdf->MultiCell(0, 10, 'Checked, Corrected and Re-Submitted to Senate Committee on Course System and Forwarded for Executive Approval on ' . date('D d m, Y'), 0, 'C');
$pdf->Ln(10);

// Set up data display

$getCourses = DB::table('results')->whereIn('approve', ['cs', 'vc'])->distinct('code')->pluck('code');
$faculties = DB::table('course')->whereIn('code', $getCourses)->distinct('faculty')->get('faculty');

foreach ($faculties as $row) {
    $programs = DB::table('course')->whereIn('code', $getCourses)->where('faculty', $row->faculty)->distinct('program')->get('program');
    $pro = DB::table('course')->whereIn('code', $getCourses)->where('faculty', $row->faculty)->distinct('program')->pluck('program');

    $p = DB::table('results')->whereIn('program', $pro)->whereIn('approve', ['vc'])->where(['session' => session('system_session')])->exists();
    //if ($p) {
        $pdf->SetFont('Times', 'BU', 12);
        $pdf->Cell(0, 10, "" . DB::table('faculty')->where('code', $row->faculty)->value('title'), 0, 1);
        $pdf->Ln(2);  // Line spacing
    //}

    foreach ($programs as $rowp) {
        // Check if a new page is needed
        if ($pdf->GetY() > 250) { // Adjust this value based on your page layout
            $pdf->AddPage();
        }
        $l1 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '100'])->count();
        $l2 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '200'])->count();
        $l3 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '300'])->count();
        $l4 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '400'])->count();
        $l5 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '500'])->count();
        $l6 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '600'])->count();

        $ll1 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '100'])->pluck('code');
        $ll2 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '200'])->pluck('code');
        $ll3 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '300'])->pluck('code');
        $ll4 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '400'])->pluck('code');
        $ll5 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '500'])->pluck('code');
        $ll6 = DB::table('program_course_registration')->where(['program' => $rowp->program, 'level' => '600'])->pluck('code');

        $r1 = DB::table('results')->whereIn('code', $ll1)->where(['session' => session('system_session'), 'approve' => 'vc'])->distinct('code')->count();
        $r2 = DB::table('results')->whereIn('code', $ll2)->where(['session' => session('system_session'), 'approve' => 'vc'])->distinct('code')->count();
        $r3 = DB::table('results')->whereIn('code', $ll3)->where(['session' => session('system_session'), 'approve' => 'vc'])->distinct('code')->count();
        $r4 = DB::table('results')->whereIn('code', $ll4)->where(['session' => session('system_session'), 'approve' => 'vc'])->distinct('code')->count();
        $r5 = DB::table('results')->whereIn('code', $ll5)->where(['session' => session('system_session'), 'approve' => 'vc'])->distinct('code')->count();
        $r6 = DB::table('results')->whereIn('code', $ll6)->where(['session' => session('system_session'), 'approve' => 'vc'])->distinct('code')->count();
        $results = DB::table('results')->whereIn('approve', ['cs', 'cs'])->where(['program' => $rowp->program])->select('code')->groupBy('code')->get();
        $code = '';

        foreach ($results as $rowr) {
            // Check if a new page is needed
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }

            $code .= $rowr->code . ' ';
        }
        $lvl = '';
        if ($l1 === $r1 && $l1 > 0) {
            $lvl .= 'I ';
        }
        if ($l2 === $r2 && $l2 > 0) {
            $lvl .= 'II ';
        }
        if ($l3 === $r3 && $l3 > 0) {
            $lvl .= 'III ';
        }
        if ($l4 === $r4 && $l4 > 0) {
            $lvl .= 'IV ';
        }
        if ($l5 === $r5 && $l5 > 0) {
            $lvl .= 'V ';
        }
        if ($l6 === $r6 && $l6 > 0) {
            $lvl .= 'VI ';
        }
        if ($code != '' || $lvl != '') {
            $pdf->SetFont('Times', 'U', 12);
            $pdf->Cell(0, 10, "DEPARTMENT OF " . DB::table('program')->where('code', $rowp->program)->value('title'), 0, 1);
            $pdf->Ln(2);  // Line spacing
            $pdf->SetFont('Times', '', 12);
        }

        if ($code != '') {
            $pdf->Cell(10, 10, "*", 0, 0); // Bullet point
            $pdf->MultiCell(0, 10, "Distribution of grades for specific courses " . $code . " in the " . session('system_session') . " Academic Session.", 0, 1);
            $pdf->Ln(3);  // Extra spacing after program section
        }

        if ($lvl != '') {
            $pdf->Cell(10, 10, "*", 0, 0); // Bullet point
            $pdf->MultiCell(0, 10, "Academic status of Part " . $lvl . "students for the 2022/2023 academic session.", 0, 1);
            $pdf->Ln(3);
        }
    }
    $pdf->Ln(5);  // Extra spacing after each faculty section
}

// Output PDF
$pdf->Output();
exit;
