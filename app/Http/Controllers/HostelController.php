<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PinImport;
use App\Models\HostelPin;
use App\Models\Hostel;
use App\Models\User;
use FPDF;
use Dompdf\Dompdf;
use Dompdf\Options;

class HostelController extends Controller
{
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
                $pinn = substr(str_shuffle(str_repeat($characters, 12)), 0, 12);

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
        if (session('accType') == 'Admin') {
            $data['hall'] = DB::table('hostel')->select('hall')->groupBy('hall')->orderBy('hall', 'asc')->get();
        } else if (session('accType') == 'Student') {
            if (session('system_session') == session('student_session')) {
                $data['hall'] = DB::table('hostel')->select('hall')->where(['flag' => 0, 'bed_type' => 2, 'status' => 0, 'gender' => session('gender')])->groupBy('hall')->orderBy('hall', 'asc')->get();
            } else {
                $data['hall'] = DB::table('hostel')->select('hall')->where(['flag' => 0, 'bed_type' => 0, 'status' => 0, 'gender' => session('gender')])->groupBy('hall')->orderBy('hall', 'asc')->get();
            }
        }
        $check = DB::table('hostel')->select('id', 'occupant', 'amount')->where('occupant', session('id_number'))->first();
        $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'occupant', 'payment_method')->where('occupant', session('id_number'))->get();
        if ($check) {
            // get amount
            $amount = $check->amount;
            $flag = 1;


        $fetching_session = \App\Http\Controllers\SystemSettingsController::getHostelFeesSession();
        $data['invoice'] = DB::table('invoices')->where(['username' => session('id'), 'description' => 'HOSTEL-MAINTENANCE/FEES', 'amount' => $amount, 'session' => $fetching_session])->get();
        $checks = DB::table('invoices')->where(['username' => session('id'), 'description' => 'HOSTEL-MAINTENANCE/FEES', 'session' => $fetching_session, 'amount' => $amount])->first();
        if ($checks && $check) {
            $flag = 2;
            $status = 'Pending';
            $run = DB::table('invoices')->select('rrr', 'status')->where(['username' => session('id'), 'description' => 'HOSTEL-MAINTENANCE/FEES', 'session' => $fetching_session, 'amount' => $amount])->get();
            $data['invoice'] = DB::table('invoices')->where(['username' => session('id'), 'description' => 'HOSTEL-MAINTENANCE/FEES', 'session' => $fetching_session, 'amount' => $amount])->get();
            foreach ($run as $row) {
                $data['rrr'] = $row->rrr;
                $status = $row->status;
            }
            // if($status == 'Paid'){
            //     $flag = 3;
            // }
        }

        }
        $payment = DB::table('hostel')->select('hostel_payment')->where('occupant', session('id_number'))->value('hostel_payment');
        if ($payment == 1) {
            $flag = 3;
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
        if (session('accType') == 'Admin') {
            $data = DB::table('hostel')->select('block')->where(['hall' => $request->hall])->groupBy('block')->orderBy('block', 'asc')->get();
        } else if (session('accType') == 'Student') {
            if (session('system_session') == session('student_session')) {
                $data = DB::table('hostel')->select('block')->where(['flag' => 0, 'status' => 0, 'gender' => session('gender'), 'hall' => $request->hall, 'bed_type' => 2])->groupBy('block')->orderBy('block', 'asc')->get();
            } else {
                $data = DB::table('hostel')->select('block')->where(['flag' => 0, 'status' => 0, 'gender' => session('gender'), 'hall' => $request->hall, 'bed_type' => 0])->groupBy('block')->orderBy('block', 'asc')->get();
            }
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
        if (session('accType') == 'Admin') {
            $data = DB::table('hostel')->select('room')->where(['hall' => $request->hall, 'block' => $request->block])->groupBy('room')->orderBy('room', 'asc')->get();
        } else if (session('accType') == 'Student') {
            if (session('system_session') == session('student_session')) {
                $data = DB::table('hostel')->select('room')->where(['flag' => 0, 'status' => 0, 'gender' => session('gender'), 'hall' => $request->hall, 'block' => $request->block, 'bed_type' => 2])->groupBy('room')->orderBy('room', 'asc')->get();
            } else {
                $data = DB::table('hostel')->select('room')->where(['flag' => 0, 'status' => 0, 'gender' => session('gender'), 'hall' => $request->hall, 'block' => $request->block, 'bed_type' => 0])->groupBy('room')->orderBy('room', 'asc')->get();
            }
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
        if (session('accType') == 'Admin') {
            $data = DB::table('hostel')->select('bed')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room])->groupBy('bed')->orderBy('bed', 'asc')->get();
        } else if (session('accType') == 'Student') {
            if (session('system_session') == session('student_session')) {
                $data = DB::table('hostel')->select('bed')->where(['flag' => 0, 'status' => 0, 'gender' => session('gender'), 'hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed_type' => 2])->groupBy('bed')->orderBy('bed', 'asc')->get();
            } else {
                $data = DB::table('hostel')->select('bed')->where(['flag' => 0, 'status' => 0, 'gender' => session('gender'), 'hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed_type' => 0])->groupBy('bed')->orderBy('bed', 'asc')->get();
            }
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
        if (session('id_number') == null || session('id_number') == '') {
            return redirect()->back()->with('error', 'Get ID Number Before Applying for Hostel');
        }
        $pin = DB::table('hostel_pin')->select('id', 'username')->where('username', session('id_number'))->first();
        if (!$pin) {
            return redirect()->back()->with('error', 'You need to have PIN before Applying');
        }
        if (session('system_session') == session('student_session') && (session('system_session') != '' && session('system_session') != null)) {
            DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed' => $request->bed, 'status' => 0, 'bed_type' => 2])->update([
                'occupant' => session('id_number'),
                'status' => 1,
            ]);
        } else {
            $check = DB::table('hostel')->select('occupant')->where('occupant', session('id_number'))->first();
            if ($check) {
                return redirect()->back()->with('error', 'You Cannot have MULTIPLE bed space');
            }
            DB::table('hostel')->where(['hall' => $request->hall, 'block' => $request->block, 'room' => $request->room, 'bed' => $request->bed, 'status' => 0, 'bed_type' => 0])->update([
                'occupant' => session('id_number'),
                'status' => 1,
            ]);
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
        if ($request->has('_token')) {
            $data = $request->all();
            unset($data['_token']);
            $filteredData = array_filter($data);
            $query = DB::table('hostel');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->select('id', 'hall', 'block', 'room', 'bed', 'status', 'room_type', 'occupant')->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        } else {
            $data['data'] = DB::table('hostel')->where(['status' => '900'])->get();
        }

        //$data['data'] = DB::table('hostel')->select('id','hall','block','room','bed','status','room_type','occupant')->where(['bed_type' => 1])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        $data['hall'] = DB::table('hostel')->select('hall')->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['page'] = 'available bed space';
        return view('main', $data);
    }

    public function recipients(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data['data'] = DB::table('hostel')->where(['flag' => 3, 'status' => 1])->select('id', 'hall', 'block', 'room', 'bed', 'status', 'bed_type', 'occupant')->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();

        if ($request->has('filter')) {
            $data = $request->all();
            //print_r($data);
            //die;
            $desiredDateTime =  $data['date'];
            unset($data['_token']);
            unset($data['filter']);
            unset($data['date']);
            $filteredData = array_filter($data);
            $query = DB::table('hostel')->join('students', 'hostel.occupant', '=', 'students.username')->join('invoices', 'students.user_id', '=', 'invoices.username')->where('invoices.updated_at', '>', $desiredDateTime)->where('invoices.description', 'HOSTEL-MAINTENANCE/FEES');
            foreach ($filteredData as $key => $value) {
                $query->where('hostel.' . $key, $value);
            }
            $data['data'] = $query->where(['hostel.status' => 1])->select('hostel.id', 'hostel.hall', 'hostel.block', 'hostel.room', 'hostel.bed', 'hostel.status', 'hostel.bed_type', 'hostel.occupant', 'hostel.hostel_payment')->orderBy('hostel.hall', 'asc')->orderBy('hostel.block', 'asc')->orderBy('hostel.room', 'asc')->orderBy('hostel.bed', 'asc')->get();
        }
        if ($request->has('hostel')) {
            $data = $request->all();
            //print_r($data);
            //die;
            unset($data['_token']);
            unset($data['hostel']);
            unset($data['date']);
            $filteredData = array_filter($data);
            $query = DB::table('hostel');
            foreach ($filteredData as $key => $value) {
                $query->where($key, $value);
            }
            $data['data'] = $query->select('id', 'hall', 'block', 'room', 'bed', 'status', 'bed_type', 'occupant', 'hostel_payment')->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        }
        $data['hall'] = DB::table('hostel')->select('hall')->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['page'] = 'recipients';
        return view('main', $data);
    }

    public function bedSpace2(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'status', 'room_type', 'occupant', 'hostel_payment')->where(['bed_type' => 0, 'flag' => 1])->whereIn('hall', ['BOT', 'NEW MALE A', 'NEW MALE B', 'AISHA MUHAMMADU BUHARI', 'NEW FEMALE', 'AISHA'])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        $data['hall'] = DB::table('hostel')->select('hall')->whereIn('hall', ['BOT', 'NEW MALE AA', 'NEW MALE BB', 'AISHA MUHAMMADU BUHARI'])->where(['bed_type' => 0])->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['page'] = 'available bed space2';
        return view('main', $data);
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
        //
        //$data['batch'] = DB::table('hostel_pin')->select('batch','flag','id')->distinct()->where(['flag' => 1])->orderBy('id', 'desc')->get();
        $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'status', 'room_type', 'occupant')->where(['bed_type' => 0])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        $data['hall'] = DB::table('hostel')->select('hall')->where(['bed_type' => 0])->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['page'] = 'online bed space';
        return view('main', $data);
    }

    public function online2(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //
        //$data['batch'] = DB::table('hostel_pin')->select('batch','flag','id')->distinct()->where(['flag' => 1])->orderBy('id', 'desc')->get();
        $data['data'] = DB::table('hostel')->select('id', 'hall', 'block', 'room', 'bed', 'status', 'room_type', 'occupant', 'flag')->whereIn('hall', ['NEW FEMALE', 'NEW MALE A', 'AISHA MUHAMMADU BUHARI', 'NEW FEMALE'])->where('block', 'FIRST FLOOR')->where(['bed_type' => 0, 'payment_method' => 'Online', 'occupant' => 'vacant'])->orderBy('hall', 'asc')->orderBy('block', 'asc')->orderBy('room', 'asc')->orderBy('bed', 'asc')->get();
        $data['hall'] = DB::table('hostel')->select('hall')->whereIn('hall', ['BOT', 'NEW MALE A', 'NEW MALE B', 'AISHA MUHAMMADU BUHARI'])->where(['bed_type' => 0, 'payment_method' => 'Online'])->groupBy('hall')->orderBy('hall', 'asc')->get();
        $data['page'] = 'online bed space2';
        return view('main', $data);
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
        }elseif($bedType == '0' || $bedType == '1' || $bedType == '2'){
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
            'bed_type' => 1,
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
        if (!session()->has('log')) {
            return redirect('/');
        }
        DB::table('hostel')->where(['id' => $request->id])->update([
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
        if(session('accType') == 'Admin'){

        }else{
            $getHostel = DB::table('hostel')->where(['occupant' => session('id_number')])->first();
            $getID = $getHostel->id;
            //$getOccupant = $getHostel->occupant;
            if($getID != $id){
                return redirect()->back()->with('error', 'Your ID No:'.session('id_number').' is captured for security reason...');
            }
        }
        $data['id'] = $id;
        return view('pdf/permit', $data);
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
