<?php

namespace App\Http\Controllers;

putenv('HOME=' . storage_path());

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use NcJoes\OfficeConverter\OfficeConverter;

class CommitteeMeetingsController extends Controller
{
    //
    //
    public function __construct(Request $req)
    {
        // Module Data
        $contents = $req->segment(1);
        $contents = str_replace("create ", "", $contents);
        $contents = str_replace("upload ", "", $contents);
        $contents = str_replace("download ", "", $contents);
        $contents = str_replace("update ", "", $contents);
        $contents = str_replace("delete ", "", $contents);
        $this->page = $contents;
        $this->table = str_replace(" ", "_", $this->page);
        $this->title = strtoupper($this->page);
    }

    public function index()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['data'] = DB::table($this->table)->orderBy('id', 'DESC')->get();
        $data['committee'] = DB::table('committee')->orderBy('name', 'ASC')->get();
        $data['sub_committee'] = DB::table('sub_committee')->orderBy('name', 'ASC')->get();
        $data['role'] = DB::table('committee_role')->orderBy('name', 'ASC')->get();
        $data['staff'] = DB::table('staff')->select('username', 'name')->orderBy('username', 'ASC')->get();
        $data['page'] = $this->page;
        $data['title'] = $this->title;
        return view('main', $data);
    }

    public function create(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Define directories
        $agenda1Path = 'agenda1';
        $agenda2Path = 'agenda2';
        $papersPath = 'papers';

        // Generate unique file names using session ID
        $sessionId = time();

        if ($request->page == 'member') {
            $sessionId = 'meet_' . ($request->meeting_id) . session('id');

            if ($request->file('agenda1')) {
                $agenda1FileName = $sessionId . '_agenda1.' . $request->file('agenda1')->getClientOriginalExtension();
                $agenda1StoredPath = $request->file('agenda1')->storeAs($agenda1Path, $agenda1FileName, 'public');

                DB::table('committee_meeting_activities')->updateOrInsert(
                    ['meeting_id' => $request->meeting_id, 'username' => session('username')],
                    [
                        'meeting_id' => $request->meeting_id,
                        'username' => session('username'),
                        'agenda1' => '/storage/' . $agenda1StoredPath,
                        'updated_at' => now(),
                    ]
                );
            }

            if ($request->file('agenda2')) {
                $agenda2FileName = $sessionId . '_agenda2.' . $request->file('agenda2')->getClientOriginalExtension();
                $agenda2StoredPath = $request->file('agenda2')->storeAs($agenda2Path, $agenda2FileName, 'public');

                DB::table('committee_meeting_activities')->updateOrInsert(
                    ['meeting_id' => $request->meeting_id, 'username' => session('username')],
                    [
                        'meeting_id' => $request->meeting_id,
                        'username' => session('username'),
                        'agenda2' => '/storage/' . $agenda2StoredPath,
                        'updated_at' => now(),
                    ]
                );
            }

            if ($request->has('papers')) {
                foreach ($request->papers as $index => $paper) {
                    if (isset($paper['file'])) {
                        $paperFileName = $sessionId . '_paper_' . $index . '.' . $paper['file']->getClientOriginalExtension();
                        $paperStoredPath = $paper['file']->storeAs($papersPath, $paperFileName, 'public');

                        // Convert and store as PDF if needed
                        $paperPdfPath = $this->convertToPdf($paperStoredPath, $papersPath, $sessionId . '_paper_' . $index . '.pdf');

                        DB::table('committee_meeting_activities')->updateOrInsert(
                            ['meeting_id' => $request->meeting_id, 'username' => session('username')],
                            [
                                'meeting_id' => $request->meeting_id,
                                'username' => session('username'),
                                'updated_at' => now(),
                            ]
                        );

                        $row_id = DB::table('committee_meeting_activities')->where('meeting_id', $request->meeting_id)->where('username', session('username'))->value('id');

                        // Save paper details to committee_uploads table
                        DB::table('committee_uploads')->insert([
                            'table_name' => 'committee_meeting_activities',
                            'row_id' => $row_id,
                            'title' => $paper['title'],
                            'file_path' => $paperPdfPath,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            return redirect()->back()->with('success', 'File Uploaded!!!');
        } else {
            $agenda1FileName = $sessionId . '_agenda1.' . $request->file('agenda1')->getClientOriginalExtension();
            $agenda2FileName = $sessionId . '_agenda2.' . $request->file('agenda2')->getClientOriginalExtension();

            // Store original Word files
            $agenda1StoredPath = $request->file('agenda1')->storeAs($agenda1Path, $agenda1FileName, 'public');
            $agenda2StoredPath = $request->file('agenda2')->storeAs($agenda2Path, $agenda2FileName, 'public');

            // Convert and store as PDF
            $agenda1PdfPath = $this->convertToPdf($agenda1StoredPath, $agenda1Path, $sessionId . '_agenda1.pdf');
            $agenda2PdfPath = $this->convertToPdf($agenda2StoredPath, $agenda2Path, $sessionId . '_agenda2.pdf');

            // Save main record to database
            $meetingId = DB::table('committee_meetings')->insertGetId([
                'committee' => $request->committee,
                'sub_committee' => $request->sub_committee,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'agenda1' => $agenda1PdfPath,
                'agenda2' => $agenda2PdfPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Handle dynamic papers
            if ($request->has('papers')) {
                foreach ($request->papers as $index => $paper) {
                    if (isset($paper['file'])) {
                        $paperFileName = $sessionId . '_paper_' . $index . '.' . $paper['file']->getClientOriginalExtension();
                        $paperStoredPath = $paper['file']->storeAs($papersPath, $paperFileName, 'public');

                        // Convert and store as PDF if needed
                        $paperPdfPath = $this->convertToPdf($paperStoredPath, $papersPath, $sessionId . '_paper_' . $index . '.pdf');

                        // Save paper details to committee_uploads table
                        DB::table('committee_uploads')->insert([
                            'table_name' => 'committee_meetings',
                            'row_id' => $meetingId,
                            'title' => $paper['title'],
                            'file_path' => $paperPdfPath,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            return redirect()->back()->with('success', 'Record Created!!!');
        }
    }


    private function convertToPdf($relativeFilePath, $savePath, $pdfFileName)
    {
        // Define paths for input DOCX file and output PDF file
        $inputFilePath = storage_path("app/public/{$relativeFilePath}");
        $outputPdfPath = storage_path("app/public/{$savePath}/{$pdfFileName}");
        // Ensure the output directory exists
        if (!Storage::exists("public/{$savePath}")) {
            Storage::makeDirectory("public/{$savePath}");
        }
        // Check if input file exists
        if (!file_exists($inputFilePath)) {
            dd("Error: Input file does not exist at {$inputFilePath}");
        }
        $inputFilePath = $inputFilePath;  // Path to your DOCX file
        //$outputDir = '/Applications/XAMPP/xamppfiles/htdocs/umstad.online/storage/app/public/agenda1';        // Path to your output directory
        // Path to the DOCX file stored in Laravel’s storage/app directory
        $docPath = $inputFilePath;
        try {
            // Initialize the OfficeConverter with the input file path
            $doc_path = \Storage::path("public/{$relativeFilePath}");
            $converter = new OfficeConverter($doc_path);
            $converter->convertTo($pdfFileName);
        } catch (\Exception $e) {
            dd("Conversion failed: " . $e->getMessage());
        }

        // Run LibreOffice command to convert DOCX to PDF
        // libreoffice --headless --convert-to pdf /path/to/sample.docx --outdir /output/directory

        // $command = "libreoffice --headless --convert-to pdf --outdir " . escapeshellarg(storage_path("app/public/{$savePath}")) . " " . escapeshellarg($inputFilePath);

        // $command = "libreoffice --headless --convert-to pdf ".escapeshellarg($inputFilePath)." --outdir ".escapeshellarg(storage_path("app/public/{$savePath}"));
        //exec($command . " 2>&1", $output, $returnCode);

        $command = "nohup /absolute/path/to/libreoffice --headless --convert-to pdf --outdir " . escapeshellarg(storage_path("app/public/{$savePath}")) . " " . escapeshellarg($inputFilePath) . " > /dev/null 2>&1 &";
        exec($command, $output, $returnCode);

        //Check the output and return code
        if ($returnCode !== 0) {
            dd("Error: LibreOffice conversion failed with return code {$returnCode}", $output);
        }

        // Check if the PDF was created successfully
        if (!file_exists($outputPdfPath)) {
            dd("Error: PDF file was not created at expected path {$outputPdfPath}");
        }

        // Return the URL path of the stored PDF
        return Storage::url("public/{$savePath}/{$pdfFileName}");
    }



    // private function convertToPdf($wordFilePath, $savePath, $pdfFileName)
    // {
    //     // Load Word document using PHPWord
    //     $phpWord = IOFactory::load($wordFilePath);
    //     $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

    //     // Save the document as HTML temporarily
    //     $tempHtmlPath = storage_path("app/public/{$savePath}/temp.html");
    //     $htmlWriter->save($tempHtmlPath);

    //     // Convert HTML to PDF using Dompdf
    //     $dompdf = new Dompdf();
    //     $dompdf->loadHtml(file_get_contents($tempHtmlPath));
    //     $dompdf->setPaper('A4', 'portrait');
    //     $dompdf->render();

    //     // Save PDF file
    //     $pdfPath = "public/{$savePath}/{$pdfFileName}";
    //     Storage::put($pdfPath, $dompdf->output());

    //     // Clean up the temporary HTML file
    //     unlink($tempHtmlPath);

    //     return Storage::url($pdfPath);  // Return the URL path of the stored PDF
    // }

    public function update(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $datas = $req->all();
        $id = $datas['id'];
        unset($datas['id']);
        unset($datas['_token']);
        $datas = array_map('strtoupper', $datas);
        DB::table($this->table)->where('id', $id)->update($datas);

        return redirect()->back()->with('success', 'Record Updated!!!');
    }

    public function delete(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $id = DB::table($this->table)->where('id', $req->id)->delete();

        return redirect()->back()->with('success', 'Record Delete!!!');
    }


    public function subCommitteeAjax(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data = DB::table('sub_committee')->select('name')->where(['committee' => $req->committee])->orderBy('name', 'asc')->get();

        $add = '<option value="">Select Option</option>';
        foreach ($data as $roww) {
            $add .= '<option value="' . $roww->name . '">' . $roww->name . '</option>';
        }

        return $add;
    }


    public function membersAjax(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data = DB::table('committee_membership')->select('name', 'username')->where(['committee' => $req->committee, 'sub_committee' => $req->sub_com])->orderBy('username', 'asc')->get();

        $add = '<option value="">Select Option</option>';
        foreach ($data as $row) {
            $add .= '<option value="' . $row->username . '">' . $row->username . '</option>';
        }

        return $add;
    }
}
