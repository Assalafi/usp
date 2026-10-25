<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PinImport;
use App\Models\HostelPin;
use App\Models\Hostel;
use App\Models\User;
use App\Models\Student;
use FPDF;
use Dompdf\Dompdf;
use Dompdf\Options;

class HostelController extends Controller
{
    /**
     * Determine whether the currently signed-in undergraduate may reserve a
     * hostel bed. This is intentionally evaluated before a reservation is
     * created; an existing reservation must never be blocked from generating
     * its payment invoice.
     */
    private function hostelEligibility(?string $idNumber): array
    {
        $duration = (int) session('duration');
        $level = (int) (session('current_level') ?? 0);
        $faculty = strtoupper(trim((string) session('faculty')));
        $idPrefix = (int) substr((string) $idNumber, 0, 2);
        $reasons = [];

        if ($duration === 4) {
            if ($idPrefix < 20) {
                $reasons[] = 'Your ID number must start with 20 or higher for a 4-year programme.';
            }
            if ($level >= 400) {
                $reasons[] = 'Your current level must be below 400 for a 4-year programme.';
            }
        } elseif ($duration === 5) {
            if ($idPrefix < 19) {
                $reasons[] = 'Your ID number must start with 19 or higher for a 5-year programme.';
            }
            if ($level >= 500) {
                $reasons[] = 'Your current level must be below 500 for a 5-year programme.';
            }
        } elseif ($duration === 6 && $faculty === 'VET') {
            if ($idPrefix < 18) {
                $reasons[] = 'Your ID number must start with 18 or higher for a VET programme.';
            }
            if ($level >= 600) {
                $reasons[] = 'Your current level must be below 600 for a VET programme.';
            }
        } elseif ($duration === 6) {
            if ($idPrefix < 22) {
                $reasons[] = 'Your ID number must start with 22 or higher for a 6-year programme.';
            }
            if ($level >= 400) {
                $reasons[] = 'Your current level must be below 400 for a 6-year programme.';
            }
        } else {
            $reasons[] = 'Your programme is not currently eligible for hostel accommodation.';
        }

        return [
            'eligible' => $reasons === [],
            'message' => 'You cannot apply for hostel accommodation at this time.',
            'reasons' => $reasons,
        ];
    }

    public function downloadPDF($fileName)
    {
        $filePath = storage_path('app/public/pdf/' . $fileName);

        if (Storage::exists('public/pdf/' . $fileName)) {
            return response()->download($filePath, $fileName);
        } else {
            return response()->json(['error' => 'File not found.']);
        }
    }

    // function generatePDFFromHTML($htmlContent) {
    //     $dompdf = new Dompdf();
    //     $dompdf->loadHtml($htmlContent);
    //     $options = new Options();
    //     $options->set('isRemoteEnabled', true);
    //     $dompdf->setOptions($options);
    //     $dompdf->render();
    //     return $dompdf->stream('generated.pdf');
    // }


    //
    public function index(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //

        $dataToStore = [];
        $batch = time();
        if ($request->pins > 0) {
            for ($i = 0; $i < $request->pins; $i++) {
                $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
                $pinn = date('Y') . substr(str_shuffle(str_repeat($characters, 12)), 0, 8);

                $dataToStore[] = ['pin' => $pinn, 'status' => 0, 'batch' => $batch, 'user' => 'System', 'time' => date('h:i:s'), 'created_at' => date('Y-m-d'), 'updated_at' => date('Y-m-d')];
            }
            DB::table('hostel_pin')->insert($dataToStore);

            //return $dataToStore;

            return redirect('/pins');
        }
        if (session('accType') == 'Admin') {
            $data['data'] = DB::table('hostel_pin')->where(['flag' => 1])->orderBy('batch', 'desc')->orderBy('id', 'desc')->get();
        } else {
            $data['data'] = DB::table('hostel_pin')->where(['flag' => 3])->orderBy('batch', 'desc')->orderBy('id', 'desc')->get();
        }


        if ($request->has('filter')) {
            $data = $request->all();
            unset($data['_token']);
            unset($data['filter']);
            $filteredData = array_filter($data);
            $query = DB::table('hostel_pin');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->get();
        }
        //$data['batch'] = DB::table('hostel_pin')->select('batch','flag','id')->distinct()->where(['flag' => 1])->orderBy('id', 'desc')->get();
        $data['batch'] = HostelPin::select('batch')->where('flag', '=', '1')->groupBy('batch')->orderBy('batch', 'desc')->get();
        $data['page'] = 'hostel pin';
        return view('main', $data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function printPin(Request $request)
    {
        if ($request->batch == 'All') {
            $data = DB::table('hostel_pin')->where(['flag' => 1])->orderBy('batch', 'desc')->orderBy('id', 'desc')->get();
        } else {
            $data = DB::table('hostel_pin')->where(['flag' => 1, 'batch' => $request->batch])->orderBy('batch', 'desc')->orderBy('id', 'desc')->get();
        }

        // Create a PDF object
        $pdf = new FPDF();
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('Arial', '', 10);

        $counter = 0; // Counter to keep track of items in each row
        $hd = 0;

        // Loop through the data
        foreach ($data as $pin) {
            if ($hd == 0) {
                for ($i = 0; $i < 3; $i++) {
                    $pdf->Cell(60, 5, 'UNIVERSITY OF MAIDUGURI', 0, 0, 'C');
                }
                $pdf->Ln();
                for ($i = 0; $i < 3; $i++) {
                    $pdf->Cell(60, 5, 'Student Affairs Division', 0, 0, 'C');
                }
                $pdf->Ln();
            }
            $pdf->Cell(60, 8, 'Hostel Pin:' . $pin->pin, 0, 0, 'C');
            $hd++;

            if ($hd == 3) {
                $pdf->Ln(17);
                $hd = 0;
            }
        }

        // Output the PDF as a download
        $pdf->Output();
        exit;
    }

    public function delete(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if ($request->batch == 'All') {
            DB::table('hostel_pin')->where(['flag' => 1])->orderBy('batch', 'desc')->delete();
        } else {
            DB::table('hostel_pin')->where(['flag' => 1, 'batch' => $request->batch])->delete();
        }
        return redirect()->back()->with('success', 'Done!!!');
    }

    public function applyHostel(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $flag = 0;
        $isStudent = session('accType') == 'Student';
        $studentId = session('id_number');
        $eligibility = $isStudent
            ? $this->hostelEligibility($studentId)
            : ['eligible' => true, 'message' => '', 'reasons' => []];
        $data['hostelApplicationOpen'] = (string) SystemSettingsController::get('hostel_application_status', '1') === '1';
        $data['hostelClosedMessage'] = SystemSettingsController::sanitizeRichText(SystemSettingsController::get(
            'hostel_closed_message',
            'Hostel applications are currently closed. Please check back later or contact Student Affairs.'
        ));
        $data['hostelAnnouncement'] = SystemSettingsController::sanitizeRichText(SystemSettingsController::get('hostel_announcement', ''));
        $data['hostelPins'] = collect();
        $data['eligibility'] = $eligibility;

        $check = DB::table('hostel')->select('id', 'occupant', 'amount')->where('occupant', $studentId)->first();
        $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'occupant', 'payment_method', 'hostel_payment')->where('occupant', $studentId)->get();

        // Do not scan the availability table when the student already has a
        // reservation or applications are closed. The short cache absorbs the
        // same hall request from many students arriving together.
        if (session('accType') == 'Admin') {
            $data['hall'] = DB::table('hostel')->select('hall')->groupBy('hall')->orderBy('hall', 'asc')->get();
        } elseif ($isStudent && !$check && $data['hostelApplicationOpen'] && $eligibility['eligible']) {
            $new_student_session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
            $bedType = $new_student_session == session('student_session') ? 2 : 0;
            $gender = session('gender');
            $hallCacheKey = 'hostel:available-halls:' . sha1((string) $gender . '|' . $bedType);
            $data['hall'] = Cache::remember($hallCacheKey, now()->addSeconds(5), function () use ($gender, $bedType) {
                return DB::table('hostel')
                    ->select('hall')
                    ->where(['flag' => 0, 'bed_type' => $bedType, 'status' => 0, 'gender' => $gender])
                    ->groupBy('hall')
                    ->orderBy('hall', 'asc')
                    ->get();
            });
        } else {
            $data['hall'] = collect();
        }

        if ($isStudent) {
            $pinCacheKey = 'hostel:pins:' . sha1((string) $studentId);
            $data['hostelPins'] = Cache::remember($pinCacheKey, now()->addSeconds(30), function () use ($studentId) {
                return DB::table('hostel_pin')
                    ->select('id', 'username', 'pin', 'status', 'batch')
                    ->where('username', $studentId)
                    ->orderBy('id')
                    ->get();
            });
        }

        if ($check) {
            // get amount
            $amount = $check->amount;
            $flag = 1;

            $fetching_session = SystemSettingsController::getHostelFeesSession();
            $data['invoice'] = DB::table('invoices')
                ->where([
                    'username' => session('id'),
                    'description' => 'HOSTEL-MAINTENANCE/FEES',
                    'amount' => $amount,
                    'session' => $fetching_session,
                ])
                ->get();
            if ($data['invoice']->isNotEmpty()) {
                $flag = 2;
                $data['rrr'] = $data['invoice']->last()->rrr;
            }
        }

        if ($data['data']->contains(fn ($row) => (string) $row->hostel_payment === '1')) {
            $flag = 3;
        }

        if ($isStudent && !$check) {
            if (!$data['hostelApplicationOpen']) {
                $flag = 4;
            } elseif (!$eligibility['eligible']) {
                $flag = 5;
            }
        }
        //dd($flag);
        $data['page'] = 'apply hostel';
        $data['flag'] = $flag;
        return view('main', $data);
    }


    public function block(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if (session('accType') == 'Student' && (string) SystemSettingsController::get('hostel_application_status', '1') !== '1') {
            return '<option value="">Hostel applications are closed</option>';
        }
        if (session('accType') == 'Admin') {
            $data = DB::table('hostel')->select('block')->where(['hall' => $request->hall])->groupBy('block')->orderBy('block', 'asc')->get();
        } else if (session('accType') == 'Student') {
            $new_student_session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
            $bedType = $new_student_session == session('student_session') ? 2 : 0;
            $gender = session('gender');
            $cacheKey = 'hostel:blocks:' . sha1((string) $gender . '|' . $bedType . '|' . $request->hall);
            $data = Cache::remember($cacheKey, now()->addSeconds(5), function () use ($gender, $bedType, $request) {
                return DB::table('hostel')
                    ->select('block')
                    ->where(['flag' => 0, 'status' => 0, 'gender' => $gender, 'hall' => $request->hall, 'bed_type' => $bedType])
                    ->groupBy('block')
                    ->orderBy('block', 'asc')
                    ->get();
            });
        }

        $add = '<option value="">Select Block</option>';
        foreach ($data as $row) {
            $add .= '<option value="' . $row->block . '">' . $row->block . '</option>';
        }

        return $add;
    }

    public function room(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if (session('accType') == 'Student' && (string) SystemSettingsController::get('hostel_application_status', '1') !== '1') {
            return '<option value="">Hostel applications are closed</option>';
        }
        if (session('accType') == 'Admin') {
            $data = DB::table('hostel')->select('room')->where(['hall' => $request->hall, 'block' => $request->block])->groupBy('room')->orderBy('room', 'asc')->get();
        } else if (session('accType') == 'Student') {

            $new_student_session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
            $bedType = $new_student_session == session('student_session') ? 2 : 0;
            $gender = session('gender');
            $cacheKey = 'hostel:rooms:' . sha1((string) $gender . '|' . $bedType . '|' . $request->hall . '|' . $request->block);
            $data = Cache::remember($cacheKey, now()->addSeconds(5), function () use ($gender, $bedType, $request) {
                return DB::table('hostel')
                    ->select('room')
                    ->where(['flag' => 0, 'status' => 0, 'gender' => $gender, 'hall' => $request->hall, 'block' => $request->block, 'bed_type' => $bedType])
                    ->groupBy('room')
                    ->orderBy('room', 'asc')
                    ->get();
            });
        }

        $add = '<option value="">Select Room</option>';
        foreach ($data as $row) {
            $add .= '<option value="' . $row->room . '">' . $row->room . '</option>';
        }

        return $add;
    }

    public function bed(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if (session('accType') == 'Student' && (string) SystemSettingsController::get('hostel_application_status', '1') !== '1') {
            return '<option value="">Hostel applications are closed</option>';
        }
        if (session('accType') == 'Admin') {
            $data = DB::table('hostel')->select('bed')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room])->groupBy('bed')->orderBy('bed', 'asc')->get();
        } else if (session('accType') == 'Student') {

            $new_student_session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();

            $bedType = $new_student_session == session('student_session') ? 2 : 0;
            $gender = session('gender');
            $cacheKey = 'hostel:beds:' . sha1((string) $gender . '|' . $bedType . '|' . $request->hall . '|' . $request->block . '|' . $request->room);
            $data = Cache::remember($cacheKey, now()->addSeconds(5), function () use ($gender, $bedType, $request) {
                return DB::table('hostel')
                    ->select('bed')
                    ->where(['flag' => 0, 'status' => 0, 'gender' => $gender, 'hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed_type' => $bedType])
                    ->groupBy('bed')
                    ->orderBy('bed', 'asc')
                    ->get();
            });
        }

        $add = '<option value="">Select Bed</option>';
        foreach ($data as $row) {
            $add .= '<option value="' . $row->bed . '">' . $row->bed . '</option>';
        }

        return $add;
    }

    public function reserveBed(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if (session('accType') == 'Student' && (string) SystemSettingsController::get('hostel_application_status', '1') !== '1') {
            return redirect()->back()->with('error', SystemSettingsController::get(
                'hostel_closed_message',
                'Hostel applications are currently closed. Please check back later.'
            ));
        }
        if (session('id_number') == null || session('id_number') == '') {
            return redirect()->back()->with('error', 'Get ID Number Before Applying for Hostel');
        }
        if (session('accType') == 'Student') {
            $eligibility = $this->hostelEligibility(session('id_number'));
            if (!$eligibility['eligible']) {
                $message = $eligibility['message'];
                if (!empty($eligibility['reasons'])) {
                    $message .= ' ' . implode(' ', $eligibility['reasons']);
                }
                return redirect()->back()->with('error', $message);
            }
        }
        $pin = DB::table('hostel_pin')->select('id', 'username')->where('username', session('id_number'))->first();
        if (!$pin) {
            return redirect()->back()->with('error', 'You need to have PIN before Applying');
        }
        $updated = 0;

            $new_student_session = \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
        if ($new_student_session == session('student_session') && (session('system_session') != '' && session('system_session') != null)) {
            $updated = DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed' => $request->bed, 'status' => 0, 'bed_type' => 2])->update([
                'occupant' => session('id_number'),
                'status' => 1,
            ]);
        } else {
            $check = DB::table('hostel')->select('occupant')->where('occupant', session('id_number'))->first();
            if ($check) {
                return redirect()->back()->with('error', 'You Cannot have MULTIPLE bed space');
            }
            $updated = DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed' => $request->bed, 'status' => 0, 'bed_type' => 0])->update([
                'occupant' => session('id_number'),
                'status' => 1,
            ]);
        }
        if ($updated !== 1) {
            return redirect()->back()->with('error', 'Sorry, that bed space was just taken. Please choose another one.');
        }
        $check = DB::table('hostel')->select('id', 'occupant')->where('occupant', session('id_number'))->first();
        if ($check) {
            $data = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'occupant')->where(['occupant' => session('id_number')])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->limit(1)->get();
            foreach ($data as $row) {
                $data['data'] = $check = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'occupant')->where('occupant', session('id_number'))->get();
                $pdf = new Dompdf();
                $options = new Options();
                $options->set('isHtml5ParserEnabled', true);
                $pdf->setOptions($options);
                $view = view('print bed', $data)->render();
                $pdf->loadHtml($view);
                $pdf->setPaper('A4', 'portrait');
                $pdf->render();
                Storage::put('public/pdf/' . session('id') . '.pdf', $pdf->output());
            }
            return redirect()->back()->with('success', 'CONGRATULATION!!!');
        } else {
            return redirect()->back()->with('error', 'Sorry!!!, Bed Space Just Taken By Someone');
        }
    }

    public function bedSpace(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $bedTypes = [
            0 => 'Online allocation',
            1 => 'Reserved allocation',
            2 => 'New student',
            3 => 'Remedial / SBRS',
        ];

        $query = DB::table('hostel')
            ->select('id', 'hall', 'block', 'room', 'bed', 'status', 'room_type', 'bed_type', 'category', 'occupant')
            ->when($request->filled('hall') && $request->input('hall') !== 'all', fn ($q) => $q->where('hall', $request->input('hall')))
            ->when($request->filled('category') && $request->input('category') !== 'all', fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('bed_type') && $request->input('bed_type') !== 'all', fn ($q) => $q->where('bed_type', (int) $request->input('bed_type')))
            ->when($request->filled('room_type') && $request->input('room_type') !== 'all', fn ($q) => $q->where('room_type', $request->input('room_type')))
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) => $q->where('status', (int) $request->input('status')))
            ->when(!$request->filled('status'), fn ($q) => $q->where('status', 0))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));
                $q->where(function ($nested) use ($search) {
                    $nested->where('hall', 'like', '%' . $search . '%')
                        ->orWhere('block', 'like', '%' . $search . '%')
                        ->orWhere('room', 'like', '%' . $search . '%')
                        ->orWhere('bed', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('hall')
            ->orderBy('block')
            ->orderBy('room')
            ->orderBy('bed');

        $statsQuery = clone $query;
        $data['stats'] = [
            'total' => (clone $statsQuery)->reorder()->count(),
            'available' => (clone $statsQuery)->reorder()->where('status', 0)->count(),
            'occupied' => (clone $statsQuery)->reorder()->where('status', 1)->count(),
        ];
        $data['data'] = $query->paginate(50)->withQueryString();
        $data['hall'] = DB::table('hostel')->select('hall')->whereNotNull('hall')->where('hall', '<>', '')->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['categories'] = DB::table('hostel')->select('category')->whereNotNull('category')->where('category', '<>', '')->groupBy('category')->orderBy('category', 'asc')->get();
        $data['roomTypes'] = DB::table('hostel')->select('room_type')->whereNotNull('room_type')->where('room_type', '<>', '')->groupBy('room_type')->orderBy('room_type', 'asc')->get();
        $data['bedTypes'] = $bedTypes;
        $data['filters'] = $request->only(['hall', 'category', 'bed_type', 'room_type', 'status', 'search']);
        $data['page'] = 'available bed space modern';
        return view('main', $data);
    }

    public function recipients(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $bedTypes = [
            0 => 'Online allocation',
            1 => 'Reserved allocation',
            2 => 'New student',
            3 => 'Remedial / SBRS',
        ];

        $query = DB::table('hostel')
            ->leftJoin('students', 'hostel.occupant', '=', 'students.username')
            ->where('hostel.status', 1)
            ->whereNotNull('hostel.occupant')
            ->where('hostel.occupant', '<>', '')
            ->select(
                'hostel.id', 'hostel.hall', 'hostel.block', 'hostel.room', 'hostel.bed',
                'hostel.status', 'hostel.bed_type', 'hostel.occupant', 'hostel.hostel_payment',
                'students.fullname as student_name'
            )
            ->when($request->filled('hall') && $request->input('hall') !== 'all', fn ($q) => $q->where('hostel.hall', $request->input('hall')))
            ->when($request->filled('bed_type') && $request->input('bed_type') !== 'all', fn ($q) => $q->where('hostel.bed_type', (int) $request->input('bed_type')))
            ->when($request->filled('hostel_payment') && $request->input('hostel_payment') !== 'all', fn ($q) => $q->where('hostel.hostel_payment', (int) $request->input('hostel_payment')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));
                $q->where(function ($nested) use ($search) {
                    $nested->where('hostel.occupant', 'like', '%' . $search . '%')
                        ->orWhere('students.fullname', 'like', '%' . $search . '%')
                        ->orWhere('hostel.hall', 'like', '%' . $search . '%')
                        ->orWhere('hostel.block', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('date'), function ($q) use ($request) {
                $date = $request->input('date');
                $q->whereExists(function ($invoice) use ($date) {
                    $invoice->select(DB::raw(1))
                        ->from('invoices')
                        ->whereColumn('invoices.username', 'students.user_id')
                        ->where('invoices.updated_at', '>', $date)
                        ->where('invoices.description', 'HOSTEL-MAINTENANCE/FEES');
                });
            })
            ->orderBy('hostel.hall')
            ->orderBy('hostel.block')
            ->orderBy('hostel.room')
            ->orderBy('hostel.bed');

        $statsQuery = clone $query;
        $data['stats'] = [
            'total' => (clone $statsQuery)->reorder()->count(),
            'paid' => (clone $statsQuery)->reorder()->where('hostel.hostel_payment', 1)->count(),
            'unpaid' => (clone $statsQuery)->reorder()->where('hostel.hostel_payment', '!=', 1)->count(),
        ];
        $data['data'] = $query->paginate(50)->withQueryString();
        $data['hall'] = DB::table('hostel')
            ->select('hall')
            ->where('status', 1)
            ->whereNotNull('occupant')
            ->where('occupant', '<>', '')
            ->whereNotNull('hall')
            ->where('hall', '<>', '')
            ->groupBy('hall')
            ->orderBy('hall', 'asc')
            ->get();
        $data['bedTypes'] = $bedTypes;
        $data['filters'] = $request->only(['hall', 'bed_type', 'hostel_payment', 'search', 'date']);
        $data['page'] = 'recipients modern';
        return view('main', $data);
    }

    public function bedSpace2(Request $request)
    {
        if (!$this->canManageAbs()) {
            return redirect('/');
        }
        $lookup = $this->absBaseQuery();
        $data['halls'] = (clone $lookup)->select('hostel.hall')->distinct()->orderBy('hostel.hall')->pluck('hall');
        $data['blocks'] = (clone $lookup)->select('hostel.block')->whereNotNull('hostel.block')->where('hostel.block', '<>', '')->distinct()->orderBy('hostel.block')->pluck('block');
        $data['rooms'] = (clone $lookup)->select('hostel.room')->whereNotNull('hostel.room')->where('hostel.room', '<>', '')->distinct()->orderBy('hostel.room')->pluck('room');
        $data['beds'] = (clone $lookup)->select('hostel.bed')->whereNotNull('hostel.bed')->where('hostel.bed', '<>', '')->distinct()->orderBy('hostel.bed')->pluck('bed');
        $data['page'] = 'available bed space2 modern';
        return view('main', $data);
    }

    protected function canManageAbs(): bool
    {
        return session()->has('log')
            && in_array((string) session('accType'), ['Admin', 'Staff'], true);
    }

    protected function absBaseQuery()
    {
        return DB::table('hostel')
            ->leftJoin('students', 'students.username', '=', 'hostel.occupant')
            ->where('hostel.bed_type', 0)
            ->where('hostel.flag', 1);
    }

    protected function absFilteredQuery(Request $request)
    {
        return $this->absBaseQuery()
            ->select([
                'hostel.id', 'hostel.hall', 'hostel.block', 'hostel.room', 'hostel.bed',
                'hostel.status', 'hostel.room_type', 'hostel.occupant',
                'hostel.hostel_payment', 'hostel.category', 'students.fullname as student_name',
            ])
            ->when($request->filled('hall') && $request->input('hall') !== 'all', fn ($q) => $q->where('hostel.hall', $request->input('hall')))
            ->when($request->filled('block') && $request->input('block') !== 'all', fn ($q) => $q->where('hostel.block', $request->input('block')))
            ->when($request->filled('room') && $request->input('room') !== 'all', fn ($q) => $q->where('hostel.room', $request->input('room')))
            ->when($request->filled('bed') && $request->input('bed') !== 'all', fn ($q) => $q->where('hostel.bed', $request->input('bed')))
            ->when($request->input('occupancy') === 'available', fn ($q) => $q->where(function ($nested) {
                $nested->where('hostel.status', 0)->orWhereNull('hostel.status');
            }))
            ->when($request->input('occupancy') === 'occupied', fn ($q) => $q->where('hostel.status', 1))
            ->when($request->input('payment') === 'paid', fn ($q) => $q->where('hostel.hostel_payment', 1))
            ->when($request->input('payment') === 'unpaid', fn ($q) => $q->where(function ($nested) {
                $nested->whereNull('hostel.hostel_payment')->orWhere('hostel.hostel_payment', '!=', 1);
            }))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));
                $q->where(function ($nested) use ($search) {
                    $nested->where('hostel.hall', 'like', '%' . $search . '%')
                        ->orWhere('hostel.block', 'like', '%' . $search . '%')
                        ->orWhere('hostel.room', 'like', '%' . $search . '%')
                        ->orWhere('hostel.bed', 'like', '%' . $search . '%')
                        ->orWhere('hostel.occupant', 'like', '%' . $search . '%')
                        ->orWhere('students.fullname', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('hostel.hall')
            ->orderBy('hostel.block')
            ->orderBy('hostel.room')
            ->orderBy('hostel.bed');
    }

    protected function existingHostelBed(string $username, ?int $exceptId = null)
    {
        return DB::table('hostel')
            ->whereRaw("TRIM(COALESCE(occupant, '')) = ?", [$username])
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->first(['id', 'hall', 'block', 'room', 'bed']);
    }

    public function absData(Request $request)
    {
        if (!$this->canManageAbs()) {
            return response()->json(['message' => 'You are not allowed to manage flagged bed spaces.'], 403);
        }

        $perPage = min(max((int) $request->input('per_page', 100), 10), 100);
        $paginator = $this->absFilteredQuery($request)->paginate($perPage);
        $rows = collect($paginator->items())->map(function ($row) {
            $row->status_label = (int) $row->status === 1 ? 'Occupied' : 'Available';
            $row->payment_label = (int) $row->hostel_payment === 1 ? 'Paid' : 'Not paid';
            return $row;
        })->values();
        $statsQuery = $this->absFilteredQuery($request);
        $stats = [
            'total' => (clone $statsQuery)->reorder()->count(),
            'available' => (clone $statsQuery)->reorder()->where(function ($q) {
                $q->where('hostel.status', 0)->orWhereNull('hostel.status');
            })->count(),
            'occupied' => (clone $statsQuery)->reorder()->where('hostel.status', 1)->count(),
            'paid' => (clone $statsQuery)->reorder()->where('hostel.hostel_payment', 1)->count(),
        ];

        return response()->json([
            'data' => $rows,
            'stats' => $stats,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function absAssign(Request $request)
    {
        if (!$this->canManageAbs()) {
            return response()->json(['message' => 'You are not allowed to assign flagged bed spaces.'], 403);
        }

        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
            'username' => ['required', 'string', 'max:100'],
        ]);
        $username = trim($validated['username']);
        $student = Student::where('username', $username)->first();
        if (!$student) {
            return response()->json(['message' => 'No student was found with that ID number. Check the ID and try again.'], 422);
        }

        $row = DB::table('hostel')->where('id', $validated['id'])->where('bed_type', 0)->where('flag', 1)->first();
        if (!$row) {
            return response()->json(['message' => 'That flagged bed space could not be found. Refresh the list and try again.'], 404);
        }
        $existing = $this->existingHostelBed($username, (int) $row->id);
        if ($existing) {
            $location = collect([$existing->hall, $existing->block, $existing->room, $existing->bed])->filter(fn ($value) => $value !== null && $value !== '')->implode(' · ');
            return response()->json(['message' => 'This student already has a hostel bed (' . ($location ?: 'space #' . $existing->id) . '). Revoke that bed before assigning another one.'], 422);
        }
        if ((int) $row->status === 1 && trim((string) $row->occupant) !== '') {
            return response()->json(['message' => 'This bed space is already occupied. Revoke the current occupant first.'], 422);
        }

        DB::transaction(function () use ($validated, $username) {
            DB::table('hostel')->where('id', $validated['id'])->where('bed_type', 0)->where('flag', 1)->update([
                'occupant' => $username,
                'status' => 1,
            ]);
        });

        return response()->json(['message' => 'Bed space assigned successfully.', 'id' => (int) $validated['id'], 'occupant' => $username, 'name' => $student->fullname]);
    }

    public function absUnflag(Request $request)
    {
        if (!$this->canManageAbs()) {
            return response()->json(['message' => 'You are not allowed to unflag bed spaces.'], 403);
        }
        $ids = collect($request->input('ids', []))->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->unique()->values();
        if ($ids->isEmpty() || $ids->count() > 500) {
            return response()->json(['message' => 'Select between 1 and 500 bed spaces.'], 422);
        }
        $updated = DB::transaction(fn () => DB::table('hostel')->whereIn('id', $ids->all())->where('bed_type', 0)->where('flag', 1)->update(['flag' => 0]));
        return response()->json(['message' => $updated . ' bed space(s) unflagged.', 'updated' => $updated]);
    }

    public function filterHall(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $type = $request->type;
        $page = $request->page;
        if ($page == 'ajax/admin/recipients') {
            $data['data'] = DB::table('hostel')->where(['status' => 1])->select('id', 'hall', 'block', 'room', 'bed', 'status', 'bed_type', 'occupant')->where(['hall' => $request->hall])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        } else {
            $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'status', 'bed_type', 'occupant')->where(['bed_type' => $type, 'hall' => $request->hall])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        }

        return view($page, $data);
    }

    public function filterCategory(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $type = $request->type;
        $page = $request->page;
        if ($page == 'ajax/admin/recipients') {
            $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'status', 'bed_type', 'occupant')->where(['category' => $request->category])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        } else {
            $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'status', 'bed_type', 'occupant')->where(['bed_type' => $type, 'category' => $request->category])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        }

        return view($page, $data);
    }

    public function online(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $bedTypes = [
            0 => 'Online allocation',
            1 => 'Reserved allocation',
            2 => 'New student',
            3 => 'Remedial / SBRS',
        ];

        $query = DB::table('hostel')
            ->select('id', 'hall', 'block', 'room', 'bed', 'status', 'room_type', 'bed_type', 'category', 'occupant', 'payment_method', 'flag')
            ->when($request->filled('hall') && $request->input('hall') !== 'all', fn ($q) => $q->where('hall', $request->input('hall')))
            ->when($request->filled('category') && $request->input('category') !== 'all', fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('bed_type') && $request->input('bed_type') !== 'all', fn ($q) => $q->where('bed_type', (int) $request->input('bed_type')))
            ->when(!$request->filled('bed_type'), fn ($q) => $q->where('bed_type', 0))
            ->when($request->filled('room_type') && $request->input('room_type') !== 'all', fn ($q) => $q->where('room_type', $request->input('room_type')))
            ->when($request->filled('payment_method') && $request->input('payment_method') !== 'all', function ($q) use ($request) {
                if (strtolower(trim((string) $request->input('payment_method'))) === 'bank') {
                    $q->whereRaw("LOWER(TRIM(COALESCE(payment_method, ''))) = 'bank'");
                } else {
                    $q->where(function ($nested) {
                        $nested->whereNull('payment_method')
                            ->orWhereRaw("LOWER(TRIM(COALESCE(payment_method, ''))) <> 'bank'");
                    });
                }
            })
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) => $q->where('status', (int) $request->input('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));
                $q->where(function ($nested) use ($search) {
                    $nested->where('hall', 'like', '%' . $search . '%')
                        ->orWhere('block', 'like', '%' . $search . '%')
                        ->orWhere('room', 'like', '%' . $search . '%')
                        ->orWhere('bed', 'like', '%' . $search . '%')
                        ->orWhere('occupant', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('hall')
            ->orderBy('block')
            ->orderBy('room')
            ->orderBy('bed');

        $statsQuery = clone $query;
        $data['stats'] = [
            'total' => (clone $statsQuery)->reorder()->count(),
            'available' => (clone $statsQuery)->reorder()->where('status', 0)->count(),
            'occupied' => (clone $statsQuery)->reorder()->where('status', 1)->count(),
        ];
        $data['data'] = $query->paginate(50)->withQueryString();
        $hallQuery = DB::table('hostel')->select('hall')->whereNotNull('hall')->where('hall', '<>', '');
        if (!$request->filled('bed_type') || $request->input('bed_type') !== 'all') {
            $hallQuery->where('bed_type', $request->filled('bed_type') ? (int) $request->input('bed_type') : 0);
        }
        $data['hall'] = $hallQuery->groupBy('hall')->orderBy('hall', 'asc')->get();
        $categoryQuery = DB::table('hostel')->select('category')->whereNotNull('category')->where('category', '<>', '');
        $data['categories'] = $categoryQuery->groupBy('category')->orderBy('category', 'asc')->get();
        $data['bedTypes'] = $bedTypes;
        $data['filters'] = $request->only(['hall', 'category', 'bed_type', 'room_type', 'payment_method', 'status', 'search']);
        $data['page'] = 'online bed space modern';
        return view('main', $data);
    }

    protected function obsHalls(): array
    {
        return [
            'NEW FEMALE',
            'AISHA MUHAMMADU BUHARI',
            'NEW MALE A',
            'NEW MALE B',
        ];
    }

    protected function canManageObs(): bool
    {
        return session()->has('log')
            && in_array((string) session('accType'), ['Admin', 'Staff'], true);
    }

    protected function obsBaseQuery()
    {
        return DB::table('hostel')
            ->whereIn('hall', $this->obsHalls())
            ->where('bed_type', 0)
            ->whereRaw("LOWER(TRIM(COALESCE(payment_method, ''))) = 'online'");
    }

    protected function obsFilteredQuery(Request $request)
    {
        $query = $this->obsBaseQuery()
            ->select([
                'id', 'hall', 'block', 'room', 'bed', 'status', 'room_type',
                'occupant', 'flag', 'category', 'payment_method',
            ])
            ->when($request->filled('hall') && $request->input('hall') !== 'all', fn ($q) => $q->where('hall', $request->input('hall')))
            ->when($request->filled('block') && $request->input('block') !== 'all', fn ($q) => $q->where('block', $request->input('block')))
            ->when($request->filled('room') && $request->input('room') !== 'all', fn ($q) => $q->where('room', $request->input('room')))
            ->when($request->filled('bed') && $request->input('bed') !== 'all', fn ($q) => $q->where('bed', $request->input('bed')))
            ->when($request->input('status') === 'available', fn ($q) => $q->where('status', 0))
            ->when($request->input('status') === 'occupied', fn ($q) => $q->where('status', 1))
            ->when($request->input('flag') === 'flagged', fn ($q) => $q->where('flag', 1))
            ->when($request->input('flag') === 'unflagged', fn ($q) => $q->where(function ($nested) {
                $nested->whereNull('flag')->orWhere('flag', 0);
            }))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));
                $q->where(function ($nested) use ($search) {
                    $nested->where('hall', 'like', '%' . $search . '%')
                        ->orWhere('block', 'like', '%' . $search . '%')
                        ->orWhere('room', 'like', '%' . $search . '%')
                        ->orWhere('bed', 'like', '%' . $search . '%')
                        ->orWhere('occupant', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('hall')
            ->orderBy('block')
            ->orderBy('room')
            ->orderBy('bed');

        return $query;
    }

    public function online2(Request $request)
    {
        if (!$this->canManageObs()) {
            return redirect('/');
        }

        $lookup = $this->obsBaseQuery();
        $data['halls'] = (clone $lookup)->select('hall')->distinct()->orderBy('hall')->pluck('hall');
        $data['blocks'] = (clone $lookup)->select('block')->whereNotNull('block')->where('block', '<>', '')->distinct()->orderBy('block')->pluck('block');
        $data['rooms'] = (clone $lookup)->select('room')->whereNotNull('room')->distinct()->orderBy('room')->pluck('room');
        $data['beds'] = (clone $lookup)->select('bed')->whereNotNull('bed')->distinct()->orderBy('bed')->pluck('bed');
        $data['page'] = 'online bed space2 modern';

        return view('main', $data);
    }

    public function online2Data(Request $request)
    {
        if (!$this->canManageObs()) {
            return response()->json(['message' => 'You are not allowed to manage online bed spaces.'], 403);
        }

        $perPage = min(max((int) $request->input('per_page', 50), 10), 100);
        $query = $this->obsFilteredQuery($request);
        $paginator = $query->paginate($perPage);

        $rows = collect($paginator->items())->map(function ($row) {
            $row->status_label = (int) $row->status === 1 ? 'Occupied' : 'Available';
            $row->flag_label = (int) ($row->flag ?? 0) === 1 ? 'Flagged' : 'Unflagged';
            $row->payment_label = 'Online';
            return $row;
        })->values();

        $statsQuery = $this->obsFilteredQuery($request);
        $stats = [
            'total' => (clone $statsQuery)->reorder()->count(),
            'available' => (clone $statsQuery)->reorder()->where('status', 0)->count(),
            'occupied' => (clone $statsQuery)->reorder()->where('status', 1)->count(),
            'flagged' => (clone $statsQuery)->reorder()->where('flag', 1)->count(),
        ];

        return response()->json([
            'data' => $rows,
            'stats' => $stats,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    protected function updateObsFlags(Request $request, int $flag): \Illuminate\Http\JsonResponse
    {
        if (!$this->canManageObs()) {
            return response()->json(['message' => 'You are not allowed to manage online bed spaces.'], 403);
        }

        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty() || $ids->count() > 500) {
            return response()->json(['message' => 'Select between 1 and 500 bed spaces.'], 422);
        }

        $updated = DB::transaction(function () use ($ids, $flag) {
            return $this->obsBaseQuery()
                ->whereIn('id', $ids->all())
                ->update(['flag' => $flag]);
        });

        return response()->json([
            'message' => $flag === 1 ? 'Selected bed spaces flagged.' : 'Selected bed spaces unflagged.',
            'updated' => $updated,
        ]);
    }

    public function online2Flag(Request $request)
    {
        return $this->updateObsFlags($request, 1);
    }

    public function online2Unflag(Request $request)
    {
        return $this->updateObsFlags($request, 0);
    }

    public function manageHostel(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //
        //$data['batch'] = DB::table('hostel_pin')->select('batch','flag','id')->distinct()->where(['flag' => 1])->orderBy('id', 'desc')->get();
        $data['hall'] = DB::table('hostel')->select('hall')->where(['flag' => 0])->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['hall2'] = DB::table('hostel')->select('hall')->where(['flag' => 0])->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['hall3'] = DB::table('hostel')->select('hall')->where(['flag' => 0])->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['block'] = DB::table('hostel')->select('block')->where(['flag' => 0])->groupBy('block')->orderBy('block', 'asc')->get();
        $data['page'] = 'manage hostel';
        return view('main', $data);
    }

    public function createHostel(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $session  = '2022/2023';
        $hall = strtoupper($request->hall);
        $gender = strtoupper($request->gender);
        $b_name = strtoupper($request->b_name);
        $b_number = strtoupper($request->b_number);
        $room = strtoupper($request->room);
        $r_r = strtoupper($request->r_r);
        $bed = strtoupper($request->bed);
        $b_r_1 = strtoupper($request->b_r_1);
        $b_r_2 = strtoupper($request->b_r_2);
        $category = strtoupper($request->category);
        $date = date('h:i:s d/m/y');
        $num_f = 0;
        $num_r_no = $request->room_start;
        $num_r = $num_r_no;
        $num_b_no = $request->bed_start;
        $num_b = $num_b_no;
        $block = '';
        $r_type = '0';
        $b_type = '0';
        while ($num_f < $b_number) {
            $num_r = $num_r_no;
            $num_f++;
            while ($num_r <= $room) {
                $num_b = $num_b_no;
                while ($num_b <= $bed) {
                    if (($request->block_numbering) == 'no') {
                        $block = $b_name;
                    } elseif (($request->block_numbering) == 'yes') {
                        $block = $b_name . '' . $num_f;
                    }
                    if ($r_r == 'ALL') {
                        $r_type = '1';
                        $b_type = '1';
                    } else {
                        if ($num_r == $r_r || $num_b == $b_r_1  || $num_b == $b_r_2) {
                            if ($num_r == $r_r) {
                                $r_type = '1';
                                $b_type = '1';
                            }
                            if ($num_b == $b_r_1  || $num_b == $b_r_2) {
                                $b_type = '1';
                            }
                        } else {
                            $r_type = '0';
                            $b_type = '0';
                        }
                    }
                    $check = DB::table('hostel')->where(['hall' => $hall, 'block' => $block, 'room' => $num_r, 'bed' => $num_b])->first();
                    if ($check) {
                        return redirect()->back()->with('error', 'Bed Space Already Exist!!!');
                    } else {
                        $id = DB::table('hostel')->insertGetId([
                            'hall' => $hall,
                            'block' => $block,
                            'room' => $num_r,
                            'bed' => $num_b,
                            'room_type' => $r_type,
                            'bed_type' => $b_type,
                            'gender' => $gender,
                            'category' => $category,
                            'session' => $session,
                            'status' => '0',
                            'flag' => '0',
                            'amount' => $request->hostel_amount
                        ]);
                    }

                    $num_b++;
                }
                $num_r++;
            }
        }
        return redirect()->back()->with('success', 'Successfully Added!!!');
    }

    public function changeHall(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        DB::table('hostel')->where(['hall' => $request->hall])->update([
            'hall' => strtoupper($request->newHall)
        ]);
        DB::table('hostel')->where(['block' => $request->block])->update([
            'block' => strtoupper($request->newBlock)
        ]);
        return redirect()->back()->with('success', 'Successfully Changed');
    }

    public function deleteBed(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        if (isset($request->hall) && isset($request->block) && isset($request->room) && isset($request->bed)) {
            DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed' => $request->bed])->delete();
            return redirect()->back()->with('success', 'Done deleted by bed');
        } else if (isset($request->hall) && isset($request->block) && isset($request->room)) {
            DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room])->delete();
            return redirect()->back()->with('success', 'Done deleted by room');
        } else if (isset($request->hall) && isset($request->block)) {
            DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block])->delete();
            return redirect()->back()->with('success', 'Done deleted by block');
        } else if (isset($request->hall)) {
            DB::table('hostel')->where(['hall' => $request->hall])->delete();
            return redirect()->back()->with('success', 'Done deleted by hall');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong...');
        }
    }

    public function changeBed(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $bedType = $request->bedType;
        // payment_method
        if($bedType == 'Online' || $bedType == 'Bank'){
            $columnToUpdate = 'payment_method';
        }elseif($bedType == '0' || $bedType == '1' || $bedType == '2' || $bedType == '3'){
            $columnToUpdate = 'bed_type';
        }else{
            return redirect()->back()->with('error', 'Something Went Wrong, with bed type');
        }
        if (isset($request->hall) && isset($request->block) && isset($request->room) && isset($request->bed)) {
            DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed' => $request->bed])->update([
                $columnToUpdate => $bedType
            ]);
            return redirect()->back()->with('success', 'Done by bed');
        } else if (isset($request->hall) && isset($request->block) && isset($request->room)) {

            if (isset($request->b1)) {
                $b1 = $request->b1;
                $b2 = $request->b2;
                if ($b1 > $b2)
                    return redirect()->back()->with('error', 'Bed Range Error');
                DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room])->whereBetween('bed', [$b1, $b2])->update([
                    $columnToUpdate => $bedType,
                ]);
            } else {
                DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room])->update([
                    $columnToUpdate => $bedType,
                ]);
            }

            return redirect()->back()->with('success', 'Done by room');
        } else if (isset($request->hall) && isset($request->block)) {
            if (!isset($request->r1))
                return redirect()->back()->with('error', 'You Must Select Room Range');
            $r1 = $request->r1;
            $r2 = $request->r2;
            if ($r1 > $r2)
                return redirect()->back()->with('error', 'Room Range Error');

            if (isset($request->b1)) {
                $b1 = $request->b1;
                $b2 = $request->b2;
                if ($b1 > $b2)
                    return redirect()->back()->with('error', 'Bed Range Error');
                DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block])->whereBetween('room', [$r1, $r2])->whereBetween('bed', [$b1, $b2])->update([
                    $columnToUpdate => $bedType,
                ]);
            } else {
                if($bedType == 'Online' || $bedType == 'Bank'){
                    DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block])->whereBetween('room', [$r1, $r2])->update([
                        'payment_method' => $bedType
                    ]);
                }else{
                    DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block])->whereBetween('room', [$r1, $r2])->update([
                        $columnToUpdate => $bedType,
                        'room_type' => $bedType
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Done by Block');
        } else if (isset($request->hall)) {
            if (!isset($request->r1))
                return redirect()->back()->with('error', 'You Must Select Room Range');
            $r1 = $request->r1;
            $r2 = $request->r2;
            if ($r1 > $r2)
                return redirect()->back()->with('error', 'Room Range Error');

            if (isset($request->b1)) {
                $b1 = $request->b1;
                $b2 = $request->b2;
                if ($b1 > $b2)
                    return redirect()->back()->with('error', 'Bed Range Error');
                DB::table('hostel')->where(['hall' => $request->hall])->whereBetween('room', [$r1, $r2])->whereBetween('bed', [$b1, $b2])->update([
                    $columnToUpdate => $bedType,
                ]);
            } else {
                DB::table('hostel')->where(['hall' => $request->hall])->whereBetween('room', [$r1, $r2])->update([
                    $columnToUpdate => $bedType,
                ]);
            }

            return redirect()->back()->with('success', 'Done by Hall');
        } else {
            return redirect()->back()->with('error', 'Something Went Wrong...');
        }
    }

    public function assignBed(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        //$exist = HostelPin::where('username', $request -> username)->first();
        $exist = 1;
        if ($exist == 1) {
            $hostel = Hostel::where('occupant', $request->username)->first();
            if ($hostel) {
                $msg = "This student (" . $request->username . ") already has bed space in " . $hostel['hall'] . " | " . $hostel['block'] . " | " . $hostel['room'] . " | " . $hostel['bed'];
                return redirect()->back()->with('error', $msg);
            }
            $genderH = Hostel::where('id', $request->id)->first();
            $genderS = User::where('username', $request->username)->first();
            //echo $genderH['gender'].' '.$genderS['gender'];
            //die;
            if ($genderS) {
                $genderS = $genderS['gender'];
                $genderH = $genderH['gender'];
                if ($genderH != $genderS) {
                    return redirect()->back()->with('error', 'Gender Error!!!');
                }
            }

            DB::table('hostel')->where(['id' => $request->id])->update([
                'occupant' => $request->username,
                'status' => 1
            ]);
            return redirect()->back()->with('success', 'Successfully Assigned');
        } else {
            return redirect()->back()->with('error', 'PIN is Required!!!');
        }
    }

    public function revoke(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        DB::table('hostel')->where(['id' => $request->id])->update([
            'occupant' => '',
            'status' => 0
        ]);
        return redirect()->back()->with('success', 'Successfully Revoke');
    }

    public function hostelPayment(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        DB::table('hostel')->where(['id' => $request->id])->update([
            'hostel_payment' => $request->hostel_payment
        ]);
        return redirect()->back()->with('success', 'Done!!!');
    }

    public function assignBed2(Request $request)
    {
        if (!$this->canManageAbs()) {
            return redirect('/');
        }
        $student = Student::where('username', trim((string) $request->username))->first();
        if (!$student) {
            return redirect()->back()->with('error', 'No student was found with that ID number.');
        }
        $existing = $this->existingHostelBed(trim((string) $request->username), (int) $request->id);
        if ($existing) {
            $location = collect([$existing->hall, $existing->block, $existing->room, $existing->bed])->filter(fn ($value) => $value !== null && $value !== '')->implode(' · ');
            return redirect()->back()->with('error', 'This student already has a hostel bed (' . ($location ?: 'space #' . $existing->id) . '). Revoke that bed before assigning another one.');
        }
        DB::table('hostel')->where(['id' => $request->id, 'bed_type' => 0, 'flag' => 1, 'status' => 0])->update([
            'occupant' => $request->username,
            'status' => 1
        ]);
        return redirect()->back()->with('success', 'Successfully Assigned');
    }

    public function flag(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        DB::table('hostel')->where(['id' => $request->id])->update([
            'flag' => 1
        ]);
        return redirect()->back()->with('success', 'Done');
    }

    public function printPermit($id)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        // Keep the legacy student URL restricted. Back-office users should use
        // the dedicated admin route below, which performs its own permission
        // and payment checks.
        if (session('accType') === 'Admin') {
            $hostel = DB::table('hostel')->where('id', $id)->first();
        } else {
            $hostel = DB::table('hostel')->where('occupant', session('id_number'))->first();

            if (!$hostel || (string) $hostel->id !== (string) $id) {
                return redirect()->back()->with('error', 'You can only download the permit assigned to your account.');
            }
        }

        if (!$hostel || empty($hostel->occupant)) {
            return redirect()->back()->with('error', 'This hostel reservation has no valid occupant or permit.');
        }

        $data['id'] = $id;
        return view('pdf/permit', $data);
    }

    /**
     * Generate a hostel permit for an authorised back-office user.
     * This deliberately uses the same permit view as the student download
     * so both copies have identical content and formatting.
     */
    public function adminPrintPermit($id)
    {
        if (!session()->has('log') || !in_array(session('accType'), ['Admin', 'Staff'], true)) {
            return redirect('/')->with('error', 'You do not have permission to download hostel permits.');
        }

        $hostel = DB::table('hostel')
            ->where('id', $id)
            ->where('status', 1)
            ->where('hostel_payment', 1)
            ->first();
        if (!$hostel || empty($hostel->occupant)) {
            return redirect()->back()->with('error', 'A permit is available only after the hostel payment has been recorded.');
        }

        $permitStudent = null;
        $isSbrsOccupant = (int) $hostel->bed_type === 3
            || str_starts_with(strtoupper(trim((string) $hostel->occupant)), 'SBRS/');

        if ($isSbrsOccupant) {
            $permitStudent = $this->lookupSbrsPermitProfile((string) $hostel->occupant);
            if (!$permitStudent) {
                return redirect()->back()->with('error', 'The SBRS student record could not be found. Confirm that the registration number is correct and try again.');
            }
        } elseif (!Student::where('username', $hostel->occupant)->exists()) {
            return redirect()->back()->with('error', 'The occupant record could not be found.');
        }

        return view('pdf/permit', [
            'id' => $hostel->id,
            'permitStudent' => $permitStudent,
        ]);
    }

    /**
     * Resolve an SBRS occupant for the shared UG permit template. SBRS
     * students intentionally do not exist in the UG students table, so the
     * old UG-only lookup incorrectly rejected their paid allocations.
     */
    private function lookupSbrsPermitProfile(string $registrationNumber): ?array
    {
        $baseUrl = rtrim((string) config('app.sbrs_profile_api_url', env(
            'SBRS_PROFILE_API_URL',
            'https://sbrs.umstad.online/internal/hostel/student'
        )), '/');
        $apiKey = (string) config('app.sbrs_profile_api_key', env(
            'SBRS_PROFILE_API_KEY',
            env('HOSTEL_API_KEY')
        ));

        if ($baseUrl === '' || $apiKey === '') {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->get($baseUrl, [
                'registration' => trim($registrationNumber),
            ]);

            if (!$response->successful()) {
                return null;
            }

            $profile = $response->json('data');
            return is_array($profile) && trim((string) ($profile['name'] ?? '')) !== ''
                ? $profile
                : null;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function uploadPin(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $batch = $request->batch;

            // Load the uploaded file using Maatwebsite/Excel
            $import = new PinImport($batch);
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }
        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }
}
