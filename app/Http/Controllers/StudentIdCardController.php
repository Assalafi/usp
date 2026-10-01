<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentIdCardController extends Controller
{
    protected string $page = "student id card";
    protected string $table = "student_id_card";
    protected string $title = "STUDENT ID CARD";
    //
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = (string) $req->segment(1);
        $contents = str_replace("create ", "", $contents);
        $contents = str_replace("upload ", "", $contents);
        $contents = str_replace("download ", "", $contents);
        $contents = str_replace("update ", "", $contents);
        $contents = str_replace("delete ", "", $contents);
        $this->page = 'student id card';
        $this->table = str_replace(" ", "_", $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index(Request $req)
    {
        //if(!session()->has('log')){return redirect('/');}
        if ($req->has('_token')) {
            $data = $req->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table('students');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->select('fullname', 'username', 'jamb_no', 'faculty', 'program', 'state_origin', 'country', 'kin_name', 'kin_phone', 'picture', 'signiture','passport_pic', 'passport_sign', 'issue_date', 'expire_date')->get();
            if(strpos($req -> faculty, '.PG') !== false || strpos($req -> username, 'PG') !== false){
                return view('pdf/pg id card', $data);
            }else{
                return view('pdf/id card', $data);
            }
            // return view('pdf/id card',$data);
        }else{

            $data['data'] = DB::table('students')->limit(1)->get();
        }
            $data['faculty'] = DB::table('faculty')->where(['status' => '1'])->select('code', 'title')->orderBy('title', 'ASC')->get();
            $data['fees_type'] = DB::table('fees_type')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
            $data['session'] = DB::table('session')->where(['status' => '1'])->select('title')->orderBy('title', 'ASC')->get();
            $data['page'] = $this->page;
            $data['title'] = $this->title;
            return view('main',$data);
    }

    /**
     * Generate a print-ready, two-sided CR80 student ID card.
     *
     * Each side is a portrait ID-1 page: 53.98mm wide by 85.6mm tall.
     */
    public function download($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            $student = Student::query()->where('id', $id)->firstOrFail();

            $isPostgraduate = Str::contains(strtoupper((string) $student->username), 'PG')
                || Str::contains(strtoupper((string) $student->faculty), '.PG');

            // Keep the established postgraduate card unchanged. The new
            // Dompdf CR80 design is intentionally for undergraduate cards only.
            if ($isPostgraduate) {
                return view('pdf/pg id card', ['id' => $id]);
            }

            $facultyTitle = DB::table('faculty')->where('code', $student->faculty)->value('title')
                ?: ($student->faculty ?: 'Not recorded');
            $programTitle = DB::table('program')->where('code', $student->program)->value('title')
                ?: ($student->program ?: 'Not recorded');

            $photoPath = $this->firstExistingPath([
                $student->picture ? public_path('storage/picture/' . $student->picture) : null,
                $student->picture ? storage_path('app/public/picture/' . $student->picture) : null,
                $student->passport_pic ? public_path('storage/passport_pic/' . $student->passport_pic) : null,
                public_path('card/default.jpg'),
            ]);

            $signaturePath = $this->firstExistingPath([
                $student->signiture ? public_path('storage/signature/' . $student->signiture) : null,
                $student->signiture ? storage_path('app/public/signature/' . $student->signiture) : null,
                $student->passport_sign ? public_path('storage/passport_sign/' . $student->passport_sign) : null,
            ]);

            $logoPath = $this->firstExistingPath([
                public_path('uploads/logo.png'),
            ]);
            $registrarSignaturePath = $this->firstExistingPath([
                public_path('card/registrar sign.png'),
            ]);

            $matric = trim((string) ($student->username ?: $student->jamb_no ?: 'Not assigned'));
            $fullName = trim((string) ($student->fullname ?: 'Student'));
            $issueDate = $this->formatCardDate($student->issue_date, now()->format('d M Y'));
            $expiryDate = $this->formatCardDate($student->expire_date, now()->copy()->addYears(5)->format('d M Y'));
            $qrData = implode("\n", array_filter([
                $matric,
                $fullName,
                $programTitle,
            ]));

            $options = new Options();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => [public_path(), storage_path()],
                'tempDir' => storage_path('app/dompdf'),
                'fontCache' => storage_path('fonts'),
                'fontDir' => storage_path('fonts'),
            ]);
            $dompdf = new Dompdf($options);
            $html = View::make('pdf.student-id-card', [
                'student' => $student,
                'facultyTitle' => $facultyTitle,
                'programTitle' => $programTitle,
                'matric' => $matric,
                'fullName' => $fullName,
                'issueDate' => $issueDate,
                'expiryDate' => $expiryDate,
                'photo' => $this->cardImage($photoPath, 21, 22.5),
                'signature' => $this->cardImage($signaturePath, 19, 6.5),
                'registrarSignature' => $this->cardImage($registrarSignaturePath, 19, 6.5),
                'logo' => $this->cardImage($logoPath, 9, 11),
                'qrData' => $this->makeQrDataUri($qrData),
                'nameText' => $this->fitCardText($dompdf, $fullName, 47, 2, 8.6, 6),
                'matricText' => $this->fitCardText($dompdf, $matric, 47, 1, 7.4, 4.8),
                'programText' => $this->fitCardText($dompdf, $programTitle, 47, 2, 6.6, 5.2),
                'facultyText' => $this->fitCardText($dompdf, $facultyTitle, 47, 2, 6, 5),
                'stateText' => $this->fitCardText($dompdf, $student->state_origin ?: 'Not recorded', 22, 1, 6, 4.5),
                'nationalityText' => $this->fitCardText($dompdf, $student->country ?: 'Not recorded', 22, 1, 6, 4.5),
                'kinText' => $this->fitCardText($dompdf, $student->kin_name ?: 'Not recorded', 47, 2, 6.2, 5),
                'kinPhoneText' => $this->fitCardText($dompdf, $student->kin_phone ?: 'Not recorded', 47, 1, 6.8, 5),
            ])->render();

            $dompdf->loadHtml($html, 'UTF-8');
            // ISO ID-1, portrait. Values are PDF points (72 points per inch).
            $dompdf->setPaper([0, 0, 153.014173, 242.645669], 'portrait');
            $dompdf->render();

            $safeName = Str::slug($matric ?: 'student');
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="student-id-card-' . $safeName . '.pdf"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            Log::error('Student ID card PDF generation failed', [
                'student_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'The student ID card could not be generated right now.');
        }
    }

    private function firstExistingPath(array $paths): string
    {
        foreach ($paths as $path) {
            if ($path && is_file($path) && is_readable($path) && @getimagesize($path)) {
                return $path;
            }
        }

        return '';
    }

    /** Explicit dimensions preserve image proportions in Dompdf (no object-fit). */
    private function cardImage(string $path, float $boxWidth, float $boxHeight): array
    {
        $size = $path !== '' ? @getimagesize($path) : false;
        if (!$size || !$size[0] || !$size[1]) {
            return ['src' => '', 'width' => 0, 'height' => 0, 'left' => 0, 'top' => 0];
        }

        $scale = min($boxWidth / $size[0], $boxHeight / $size[1]);
        $width = $size[0] * $scale;
        $height = $size[1] * $scale;

        return [
            'src' => $this->toDataUri($path),
            'width' => round($width, 3),
            'height' => round($height, 3),
            'left' => round(($boxWidth - $width) / 2, 3),
            'top' => round(($boxHeight - $height) / 2, 3),
        ];
    }

    /** Fit complete values to reserved lines using the actual PDF font metrics. */
    private function fitCardText(Dompdf $pdf, string $text, float $widthMm, int $maxLines, float $maximum, float $minimum): array
    {
        $text = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $text)));
        $metrics = $pdf->getFontMetrics();
        $font = $metrics->getFont('DejaVu Sans', 'bold');
        $width = $widthMm * 72 / 25.4;

        for ($size = $maximum; $size >= $minimum - 0.01; $size -= 0.2) {
            $lines = [];
            $line = '';
            foreach (explode(' ', $text) as $word) {
                $candidate = $line === '' ? $word : $line . ' ' . $word;
                if ($line !== '' && $metrics->getTextWidth($candidate, $font, $size) > $width) {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $line = $candidate;
                }
            }
            $lines[] = $line;
            $widest = max(array_map(fn ($value) => $metrics->getTextWidth($value, $font, $size), $lines));
            if (count($lines) <= $maxLines && $widest <= $width) {
                return ['text' => implode("\n", $lines), 'size' => round($size, 2)];
            }
        }

        // Do not silently clip or omit unusually long official record values.
        throw new \RuntimeException('An ID-card field is too long to print legibly. Please review the student record.');
    }

    private function toDataUri(string $path): string
    {
        if ($path === '' || !is_file($path)) {
            return '';
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : null;
        $mime = $mime ?: ('image/' . strtolower(pathinfo($path, PATHINFO_EXTENSION)));

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }

    private function makeQrDataUri(string $value): string
    {
        if ($value === '') {
            return '';
        }

        try {
            $options = new \chillerlan\QRCode\QROptions([
                'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                'scale' => 5,
                'imageBase64' => true,
                'quietzoneSize' => 4,
            ]);

            return (new \chillerlan\QRCode\QRCode($options))->render($value);
        } catch (\Throwable $e) {
            Log::warning('Student ID card QR generation failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function formatCardDate($value, string $fallback): string
    {
        if (!$value) {
            return $fallback;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return $fallback;
        }
    }
    public function create(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->insert($datas);
        return redirect()->back()->with('success', 'Record Created!!!');
    }

    public function update(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id',$id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if(!session()->has('log')){return redirect('/');}
        $id = DB::table($this->table)->where('id',$req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }
}
