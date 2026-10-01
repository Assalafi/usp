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
     * The card is rendered at the physical ID-1 dimensions (85.6mm x
     * 53.98mm), rather than being placed on an A4 sheet.
     */
    public function download($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            $student = Student::query()->where('id', $id)->firstOrFail();

            $facultyTitle = DB::table('faculty')->where('code', $student->faculty)->value('title')
                ?: ($student->faculty ?: 'Faculty not set');
            $departmentTitle = DB::table('department')->where('code', $student->department)->value('title')
                ?: ($student->department ?: 'Department not set');
            $programTitle = DB::table('program')->where('code', $student->program)->value('title')
                ?: ($student->program ?: 'Programme not set');

            $isPostgraduate = Str::contains(strtoupper((string) $student->username), 'PG')
                || Str::contains(strtoupper((string) $student->faculty), '.PG');

            // Keep the established postgraduate card unchanged. The new
            // Dompdf CR80 design is intentionally for undergraduate cards only.
            if ($isPostgraduate) {
                return view('pdf/pg id card', ['id' => $id]);
            }

            $photoPath = $this->firstExistingPath([
                $isPostgraduate && $student->passport_pic
                    ? public_path('storage/passport_pic/' . $student->passport_pic)
                    : null,
                $student->picture ? public_path('storage/picture/' . $student->picture) : null,
                $student->passport_pic ? public_path('storage/passport_pic/' . $student->passport_pic) : null,
                public_path('card/default.jpg'),
            ]);

            $signaturePath = $this->firstExistingPath([
                $student->signiture ? public_path('storage/signature/' . $student->signiture) : null,
                $student->passport_sign ? public_path('storage/passport_sign/' . $student->passport_sign) : null,
                public_path('card/student sign.png'),
            ]);

            $logoPath = $this->firstExistingPath([
                public_path('uploads/logo.png'),
                public_path('card/logo.png'),
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

            $html = View::make('pdf.student-id-card', [
                'student' => $student,
                'cardType' => 'UG STUDENT',
                'facultyTitle' => $facultyTitle,
                'departmentTitle' => $departmentTitle,
                'programTitle' => $programTitle,
                'matric' => $matric,
                'fullName' => $fullName,
                'issueDate' => $issueDate,
                'expiryDate' => $expiryDate,
                'photoData' => $this->toDataUri($photoPath),
                'signatureData' => $this->toDataUri($signaturePath),
                'logoData' => $this->toDataUri($logoPath),
                'qrData' => $this->makeQrDataUri($qrData),
            ])->render();

            $options = new Options();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => [public_path(), storage_path('app/public'), storage_path()],
                'tempDir' => storage_path('app/dompdf'),
                'fontCache' => storage_path('fonts'),
                'fontDir' => storage_path('fonts'),
            ]);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            // CR80 / ISO ID-1: 85.60mm x 53.98mm, landscape.
            $dompdf->setPaper([0, 0, 242.645669, 153.014173], 'landscape');
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
            if ($path && is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return '';
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
                'quietzoneSize' => 1,
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
