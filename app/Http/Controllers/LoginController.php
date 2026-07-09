<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\HostelPin;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    //
    function auth(Request $req)
    {
        // get user_id from Student table where username = $req -> email or jamb_no = $req -> email
        $user_id = Student::where('username', $req->email)
            ->orWhere('jamb_no', $req->email)
            ->limit(1)
            ->value('user_id');
        if ($user_id > 0) {
        } else {
            $user_id = User::where('username', $req->email)
                ->limit(1)
                ->value('id');
            // dd($user_id);
        }

        $check = Hash::check($req->password, User::where(['id' => $user_id])->value('password'));
        if (User::where(['id' => $user_id])->exists() && $check == 1) {
            $req->session()->put('username', $req->email);
            $data = User::select('accType', 'id', 'gender', 'level', 'program', 'faculty', 'status')->where(['id' => $user_id])->get();
            foreach ($data as $row) {
                $req->session()->put('accType', $row->accType);
                $req->session()->put('id', (string) $row->id);
                $req->session()->put('gender', $row->gender);
                $req->session()->put('level', $row->level);
                $system_session = DB::table('session')->where('status', '1')->value('title');
                $system_semester = DB::table('semester')->where('status', '1')->value('semester');
                $req->session()->put('system_session', $system_session);
                $req->session()->put('system_semester', $system_semester);

                if ($row->accType == 'Student') {
                    $entry_session = '2021/2022';
                    $entry_level = $row->level;
                    $id_number = $req->email;
                    $id_no = 0;
                    $flag = 0;

                    $stds = Student::where('user_id', $row->id)->select('session_of_entry', 'level_of_entry', 'username', 'id_no', 'program', 'faculty', 'level', 'state_origin', 'lga_origin', 'update_profile', 'duration', 'father_phone', 'level_flag')->get();
                    foreach ($stds as $std) {
                        $flag = 1;
                        $entry_session = $std->session_of_entry;
                        $entry_level = $std->level_of_entry;
                        $id_number = $std->username;
                        $id_no = $std->id_no;
                        // if $std->father_phone is null or empty string, activeProfile is 0
                        if (!empty($std->father_phone)) {
                            $activeProfile = 1;
                        } else {
                            $activeProfile = 0;
                        }
                        $req->session()->put('activeProfile', $activeProfile);
                        $req->session()->put('state', $std->state_origin);
                        $req->session()->put('lga', $std->lga_origin);
                        $req->session()->put('update_profile', $std->update_profile);
                        $req->session()->put('program', $std->program);
                        $req->session()->put('faculty', $std->faculty);
                        $req->session()->put('current_level', $std->level);
                        $req->session()->put('level', $row->level);
                        $req->session()->put('duration', $std->duration);
                        $req->session()->put('level_flag', $std->level_flag);
                        $req->session()->put('structure_id', $std->structure_id);
                    }
                    if ($flag == 0) {
                        return redirect()->back()->with('error', 'Your Record not Found on this Portal, Visit Student Affairs.');
                    }

                    $req->session()->put('session_of_entry', $entry_session);
                    $req->session()->put('level_of_entry', $entry_level);
                    $req->session()->put('id_number', $id_number);
                    $req->session()->put('id_no', $id_no);
                    if ($row->status == '0') {
                        $req->session()->flash('info', 'Update Your Password');
                        return redirect('/account validation');
                    }
                    $req->session()->put('student_session', $entry_session);
                    if (($system_session == $entry_session) && $row->accType == 'Student') {
                        $payment = DB::table('invoices')->where(['username' => $row->id, 'serviceTypeId' => '365039916', 'session' => $system_session, 'status' => 'Paid'])->select('id')->value('id');
                        // echo $payment;
                        // die;
                        if ($payment > 0) {
                            // dd('1');
                        } else {
                            $req->session()->put('payment', '1');
                            return redirect('/school-fees')->with('info', 'Proceed with your payment.');
                        }
                    }
                } elseif ($row->accType == 'Transfer') {
                    $req->session()->put('log', '1');
                    $req->session()->flash('success', 'Successfully Login');
                    return redirect('/inter-university-transfer');
                } elseif ($row->accType == 'Applicant') {
                    // Post UTME Screening is Closed
                    // return redirect()->back()->with('info', 'Post UTME Screening is Closed');

                    if ($row->status == '0') {
                        $req->session()->flash('info', 'Update Your Password');
                        return redirect('/account validation');
                    }
                    $applicant = Applicant::where('user_id', $row->id)->first();
                    if ($applicant) {
                        $paymentStatus = Invoice::where(['description' => 'POST UTME', 'session' => $system_session, 'status' => 'Paid', 'username' => $row->id])->select('id')->value('id');
                        if ($paymentStatus > 0) {
                            $req->session()->put('log', '1');
                            $req->session()->flash('success', 'Successfully Login');
                            return redirect('/application');
                        } else {
                            $req->session()->put('payment', '1');
                            return redirect('/applicant-fees')->with('info', 'Proceed with your payment.');
                        }
                    }
                } else {
                    $staffRecord = DB::table('staff')->where('username', session('username'))->select('appointment', 'unit', 'faculty', 'department', 'program')->get();
                    foreach ($staffRecord as $rec) {
                        $req->session()->put('appointment', $rec->appointment);
                        $req->session()->put('unit', $rec->unit);
                        $req->session()->put('faculty', $rec->faculty);
                        $req->session()->put('department', $rec->department);
                        $req->session()->put('program', $rec->program);
                        $req->session()->put('processed', 0);
                        $req->session()->put('total', 0);
                    }
                }
            }
            //dd('Hii');
            $req->session()->put('log', '1');
            $req->session()->flash('success', 'Successfully Login');
            return redirect('/dash');
        } else {
            // $req -> session() -> flash('errror', 'Wrong ID Or Password');
            // return redirect('/');
            return redirect()->back()->with('error', 'Wrong ID Or Password');
        }
    }

    function accountValidations(Request $request)
    {
        $data['id'] = '1';
        return view('account validation', $data);
    }

    function accountValidation(Request $request)
    {
        try {
            $this->validate($request, [
                'picture' => 'required|image|mimes:jpeg,png,jpg|max:200',  // 200 KB
            ], [
                'picture.required' => 'Please choose a file.',
                'picture.file' => 'Invalid file format.',
                'picture.mimetypes' => 'Invalid file format. Please upload a PNG, JPEG or JPEG image.',
                'picture.max' => 'File size must be less than 200KB.',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Check Image Format Or Size');
        }

        $p1 = $request->p1;
        $p2 = $request->p2;
        if ($p1 == $p2) {
            if ($request->file('picture')) {
                $dot = $request->file('picture')->getClientOriginalExtension();
                $request->file('picture')->storeAs('picture', session('id') . '.' . $dot, 'public');

                $applicant = Student::where(['user_id' => session('id')])->update([
                    'picture' => session('id') . '.' . $dot
                ]);
            }

            User::where('username', session('username'))->update([
                'password' => Hash::make($request->p1),
                'status' => '1'
            ]);
            $system_session = DB::table('session')->where('status', '1')->value('title');
            // dd($system_session);
            if ('Applicant' == session('accType')) {
                $request->session()->put('payment', '1');
                return redirect('/applicant-fees')->with('info', 'Proceed with your payment.');
            } elseif ($system_session == session('session_of_entry')) {
                $request->session()->put('payment', '1');
                return redirect('/school-fees')->with('info', 'Proceed with your payment.');
            } else if (session('session_of_entry') == '2021/2022') {
                $request->session()->put('log', '1');
                return redirect('/dash')->with('success', 'Password Updated');
            }
        } else {
            return redirect()->back()->with('error', 'Password did not matched!!!');
        }
    }

    function pin(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $data['page'] = 'validate hostel pin';
        return view('main', $data);
        // return view('validate hostel pin', $data);
    }

    function fpassword(Request $req)
    {
        $data['page'] = 'forgot password';
        return view('forgot password', $data);
    }

    function validatePin(Request $req)
    {
        $user = User::where('id', session('id'))->first();
        if ($user) {
            $pin = HostelPin::where('pin', $req->password)->first();
            if ($pin) {
                if ($pin['username'] == 'Awaiting') {
                    $exist = HostelPin::where('username', $req->email)->first();
                    if ($exist) {
                        return redirect()->back()->with('error', "You can't Have Multiple PIN");
                    }
                    HostelPin::where('id', $pin['id'])->update([
                        'username' => $req->email,
                    ]);
                    User::where('id', session('id'))->update([
                        'gender' => strtoupper($req->gender),
                    ]);

                    $req->session()->put('gender', $req->gender);
                    return redirect()->back()->with('success', 'Successfully Validated');
                } else {
                    return redirect()->back()->with('error', 'Used PIN');
                }
            } else {
                return redirect()->back()->with('error', 'Invalid PIN');
            }
        } else {
            return redirect()->back()->with('error', 'Oopss!!! Your record not found!!!');
        }
    }

    function forgot(Request $req)
    {
        $user = HostelPin::where('username', $req->email)->first();

        if ($user) {
            $exist = HostelPin::where('username', $req->email)->first();
            if ($exist['pin'] == $req->password) {
                User::where('username', $req->email)->update([
                    'password' => Hash::make($req->password),
                    'gender' => strtoupper($req->gender),
                ]);
                return redirect('/')->with('success', 'Use your PIN as Password');
            } else {
                return redirect()->back()->with('error', 'Use your initial PIN');
            }
        } else {
            return redirect()->back()->with('error', 'No validation history with this ID Number');
        }
    }

    function update(Request $req)
    {
        $p1 = $req->p1;
        $p2 = $req->p2;
        if ($p1 == $p2) {
            User::where('username', session('username'))->update([
                'password' => Hash::make($req->p1),
            ]);
            return redirect('/dash')->with('success', 'Password Updated');
        } else {
            return redirect()->back()->with('error', 'Password did not matched!!!');
        }
    }

    function resetPassword(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $username = $req->username;
        // get user_id from Student table where username = $username

        $applicant_id = Applicant::where('username', $username)->value('user_id');
        if($applicant_id > 0){
            User::where('id', $applicant_id)->update([
                'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
            ]);
            return redirect('/reset-passwords')->with('success', 'Password reset to ' . \App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026'));
        }
        $user_id = Student::where('username', $username)->value('user_id');
        if ($user_id > 0) {
            User::where('id', $user_id)->update([
                'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
            ]);
            return redirect('/reset-passwords')->with('success', 'Password reset to ' . \App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026'));
        } else {
            $user_id = Student::where('jamb_no', $username)->value('user_id');
            if ($user_id > 0) {
                User::where('id', $user_id)->update([
                    'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
                ]);
                return redirect('/reset-passwords')->with('success', 'Password reset to ' . \App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026'));
            } else {
                // maybe is staff
                $user_id = User::where('username', $username)->value('id');
                if ($user_id > 0) {
                    if (session('accType') != 'Admin') {
                        return redirect('/reset-passwords')->with('error', 'You are not authorized to reset this password');
                    }
                    User::where('id', $user_id)->update([
                        'password' => Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026')),
                    ]);
                    return redirect('/reset-passwords')->with('success', 'Password reset to ' . \App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026'));
                } else {
                    return redirect('/reset-passwords')->with('error', 'User not found');
                }
            }
        }
    }

    function updatePassword(Request $req)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $p1 = $req->p1;
        $p2 = $req->p2;

        if ($p1 == $p2) {
            $userId = $req->id ?? session('id');
            User::where('id', $userId)->update([
                'password' => Hash::make($req->p1),
            ]);
            return redirect()->back()->with('success', 'Password Updated');
        } else {
            return redirect()->back()->with('error', 'Password did not matched!!!');
        }
    }
}
