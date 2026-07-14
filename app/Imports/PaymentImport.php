<?php

namespace App\Imports;

//use App\Models\Admitted;
use App\Models\Invoice;
//use App\Models\Student;
//use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PaymentImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true;  // Flag to exclude the first row

    private $upload_type;
    private $session;
    private $sponsor;
    private $service;
    private $schoolFeesSession;

    public function __construct($upload_type, $session, $sponsor, $service)
    {
        $this->upload_type = $upload_type;
        $this->session = $session;
        $this->sponsor = $sponsor;
        $this->service = $service;
        $this->schoolFeesSession = \App\Http\Controllers\SystemSettingsController::getSchoolFeesSession();

    }

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        $nullJambNo = [];
        $errorMessage = '';
        $amountErrorStudents = [];
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue;  // Skip the first row
            }

            // Check if email field is not empty

            // echo $row[1].$this -> upload_type;
            // die;
            //dd($row[0]);
            if (!empty($row[0])) {
                $id = DB::table('users')->where('username', $row[0])->value('id');
                if ($id > 0) {
                } else {
                    $id = DB::table('students')->where('username', $row[0])->value('user_id');
                }
                //dd($id);
                $checkStudent = 0;
                $record = DB::table('students')->where('user_id', $id)->select('fullname', 'session_of_entry', 'username', 'faculty', 'department', 'program')->get();
                foreach ($record as $value) {
                    $fullname = $value->fullname;
                    $studentSession = $value->session_of_entry;
                    $checkStudent = 1;
                    $student_id = $value -> username;
                    $faculty = $value -> faculty;
                    $department = $value -> department;
                    $program = $value -> program;
                }
                //dd($checkStudent);
                if ($checkStudent == 1) {
                    $level = $row[4];

                            //dd($level);
                    // level should be from 100 to 600 else return
                    if ($level < 100 || $level > 600) {
                        session()->put('studentImportStatus', 'error');
                        session()->put('studentImportMsg', 'Level Error');
                        return;
                    }
                    // $row[5] may have comma in the amount, remove it all
                    $amount = str_replace(',', '', $row[5]);

                    $amount = (float) $amount;
                    $ref = $row[6];

                    if($this->service == 'hostel fees'){
                        $description = 'HOSTEL-MAINTENANCE/FEES';
                        $serviceTypeId = '767540443';
                        $hostel_amount = DB::table('hostel')->where('occupant', $row[0])->value('amount');
                        $amount = (float) $hostel_amount;
                        if ($amount == 0 || $amount == null || $amount == '') {
                            $amountErrorStudents[] = $student_id;
                            continue;
                        }
                        $hostel_amount = DB::table('hostel')->where('occupant', $row[0])->update([
                            'hostel_payment' => '1'
                        ]);

                    }else if($this->service == 'school fees'){
                        $description = 'UNIVERSITY OF MAIDUGURI-1000127 FEES';
                        $serviceTypeId = '365039916';
                        if ($this->upload_type == 'full') {
                            if ($amount != 0) {
                            //dd($amount);
                                $amountErrorStudents[] = $student_id;
                                $errorMessage .= 'Amount Error for the following student(s): ';
                                continue;
                            }
                            $amount = 0;
                            if ($studentSession == $this->schoolFeesSession) {
                                $amount = DB::table('school_fees')->where(['faculty' => $faculty, 'department' => $department, 'program' => $program, 'level' => $level, 'type' => 'NEW'])->orderBy('amount', 'desc')->limit(1)->value('amount');
                            } else {
                                $amount = DB::table('school_fees')->where(['faculty' => $faculty, 'department' => $department, 'program' => $program, 'level' => $level, 'type' => 'RETURNING'])->orderBy('amount', 'desc')->limit(1)->value('amount');
                            }
                            //dd($amount);
                            if ($amount == 0) {
                                $amountErrorStudents[] = $student_id;
                                $errorMessage .= 'Amount Error for the following student(s): ';
                                continue;
                            }
                            $amount = (float) $amount;
                        } else if ($this->upload_type == 'part') {
                            $amount = (float) $amount;
                            if ($amount == 0) {
                                $amountErrorStudents[] = $student_id;
                                $errorMessage .= 'Amount Error for the following student(s): ';
                                continue;
                            }
                        } else {
                            $amountErrorStudents[] = $student_id;
                            $errorMessage .= 'Amount Error for the following student(s): ';
                            continue;
                        }
                    }else{
                        session()->put('studentImportStatus', 'error');
                        session()->put('studentImportMsg', 'Service Error');
                        return;
                    }


                    $username = $row[0];

                    $datas['username'] = $id;
                    $datas['name'] = $fullname . ' (' . $username . ')';
                    $datas['phone'] = $this->sponsor;
                    $datas['email'] = $this->sponsor;
                    $datas['fees_type'] = $this->sponsor;
                    $datas['description'] = $description;
                    $datas['amount'] = $amount;
                    $datas['amount_type'] = $this->sponsor;
                    $datas['payment_method'] = 'Upload';
                    $datas['rrr'] = $ref;
                    $datas['orderId'] = $ref;
                    $datas['serviceTypeId'] = $serviceTypeId;
                    $datas['faculty'] = $faculty;
                    $datas['department'] = $department;
                    $datas['program'] = $program;
                    $datas['level'] = $level;
                    $datas['session'] = $this->session;
                    $datas['status'] = 'Paid';
                    $datas['updated_at'] = now();
                    $session = $this->session;
                    $username = $id;

                    try {
                        if($this->sponsor == 'nelfund' && $this->service == 'school fees'){
                            // first update any existing record with the same description and session and username to description = "REFUND", before adding new record
                            $id = DB::table('invoices')->where(['serviceTypeId' => '365039916', 'session' => $session, 'username' => $username, 'status' => 'Paid'])->where('fees_type', '!=', 'nelfund')->pluck('id');
                            // dd($id);
                            Invoice::whereIn('id', $id)
                                ->where('fees_type', '!=', 'nelfund')
                                ->where('session', $session)
                                ->where('description', 'UNIVERSITY OF MAIDUGURI-1000127 FEES')
                                ->update([
                                    'description' => 'REFUND',
                                    'serviceTypeId' => '365039916',
                                    'updated_at' => now()
                                ]);
                            Invoice::updateOrCreate(
                                ['rrr' => $ref],
                                $datas
                            );
                        }else{
                            Invoice::updateOrCreate(
                                ['rrr' => $ref],
                                $datas
                            );
                        }
                    } catch (QueryException $e) {
                        dd($e->getMessage(), $datas);
                    } catch (\Exception $e) {
                        dd($e->getMessage(), $datas);
                    } finally {
                    }
                } else {
                    $nullJambNo[] = $row[0];
                }
            }
        }

        // dd($nullJambNo);
        if (!empty($nullJambNo)) {
            //$errorMessage .= 'Error!!!, the record of the following student(s) are not found : ';
            foreach ($nullJambNo as $candidate) {
                $errorMessage .= (isset($candidate) ? $candidate : 'Unknown') . ',';  // Assuming $candidate[0] holds a name or identifier
            }
            $errorMessage .= ' ';
        }

        if (!empty($amountErrorStudents)) {
            $errorMessage .= 'Amount Error for the following student(s): ';
            foreach ($amountErrorStudents as $studentId) {
                $errorMessage .= $studentId . ',';
            }
        }

        if (!empty($errorMessage)) {
            // return redirect()->back()->with('error', $errorMessage);
            session()->put('studentImportStatus', 'error');
            session()->put('studentImportMsg', $errorMessage);
        } else {
            // return redirect()->back()->with('success', 'Students imported successfully.');
            session()->put('studentImportStatus', 'success');
            session()->put('studentImportMsg', 'Students imported successfully.');
        }
    }
}
