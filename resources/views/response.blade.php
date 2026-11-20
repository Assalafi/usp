<?php
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\User;
if (isset($_GET['RRR']) && isset($_GET['orderID'])) {
    $rrr = $_GET['RRR'];
    $orderId = $_GET['orderID'];

    $merchantId = env('REMITA_MERCHANT_ID');
    $apiKey = env('REMITA_API_KEY');
    $apiHash = hash('sha512', $rrr . '' . $apiKey . '' . $merchantId);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => \App\Http\Controllers\SystemSettingsController::getRemitaBaseUrl() . '/remita/exapp/api/v1/send/api/echannelsvc/' . $merchantId . '/' . $rrr . '/' . $apiHash . '/status.reg',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        // Never allow a gateway callback to occupy a PHP worker indefinitely.
        // The callback is retriable; the portal must remain available even
        // when Remita is slow or unreachable.
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $apiHash . ''],
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    $obj = json_decode($response, true);
    $obj = is_array($obj) ? $obj : [];

    // Remita does not always return every descriptive field on a callback.
    // The status and RRR are the only values needed to settle the invoice;
    // missing display fields must never turn a successful return into a 500.
    $status = (string) ($obj['status'] ?? $obj['statuscode'] ?? '');
    $amount = $obj['amount'] ?? null;
    $paymentDate = $obj['paymentDate'] ?? null;
    $transactiontime = $obj['transactiontime'] ?? null;
    $paymentstatus = (string) ($obj['message'] ?? $obj['statusMessage'] ?? '');

    if ($status == '021') {
        echo '<script>window.location.href="/payment";</script>';
    } elseif ($status == '023') {
        echo '<script>window.location.href="/payment";</script>';
    } elseif (in_array($status, ['00', '01'])) {
        $datas['status'] = 'Paid';
        $datas['updated_at'] = NOW();
        $des = DB::table('invoices')
            ->where('rrr', $rrr)
            ->whereIn('status', ['Pending', 'Processing'])
            ->select('description', 'username', 'rrr', 'hostel_id')
            ->get();
        $destination = '/dash';
        foreach ($des as $invoice) {
            if ($invoice->description == 'HOSTEL-MAINTENANCE/FEES') {
                $destination = '/bed space reservations';
                $username = DB::table('students')->where('user_id', $invoice->username)->select('username')->value('username');
                if ($username) {
                    $hostelQuery = DB::table('hostel')->where('occupant', $username);
                    if (!empty($invoice->hostel_id)) {
                        $hostelQuery->where('id', $invoice->hostel_id);
                    } else {
                        $hostelQuery->where(function ($query) {
                            $query->whereNull('hostel_payment')->orWhere('hostel_payment', '!=', '1');
                        })->orderBy('id');
                    }
                    $hostelToSettle = $hostelQuery->first(['id']);
                    if ($hostelToSettle) {
                        DB::table('hostel')->where('id', $hostelToSettle->id)->update(['hostel_payment' => '1']);
                    }
                }
            } elseif ($invoice->description == 'UNIVERSITY OF MAIDUGURI-1000127 FEES') {
                $session = DB::table('session')->where('status', '1')->value('title');
                $flag = 0;
                $noo = Student::where(['user_id' => $invoice->username, 'session_of_entry' => $session])
                    ->select('id_no')
                    ->value('id_no');
                if ($noo == 0) {
                    //while($flag == 0){
                    $data = Student::where(['user_id' => $invoice->username, 'session_of_entry' => '2023/2024'])
                        ->select('id_format', 'department', 'session_of_entry')
                        ->get();
                    foreach ($data as $row) {
                        $id_format = $row->id_format;
                        $department = $row->department;
                        $ses = $row->session_of_entry;

                        $lastId = Student::where(['session_of_entry' => $ses, 'department' => $department])
                            ->select('id_no')
                            ->orderBy('id_no', 'DESC')
                            ->limit(1)
                            ->value('id_no');
                        $id_no = ++$lastId;
                        $session = substr($ses, 2, 2);
                        $lastId = str_pad($lastId, 4, '0', STR_PAD_LEFT);
                        $id_number = $session . $id_format . $lastId;

                        $check = Student::where('username', $id_number)->select('id')->value('id');
                        if ($check > 0) {
                        } else {
                            Student::where(['user_id' => $invoice->username])
                                ->where('id_no', 0)
                                ->update([
                                    'username' => $id_number,
                                    'id_no' => $id_no,
                                ]);
                            $flag++;
                        }
                    }

                    //}
                }
            } elseif ($invoice->description == 'POST UTME') {
                // Applicants should return directly to their application form
                // after the screening fee is confirmed.
                $destination = '/application';
            }

            DB::table('invoices')->where('rrr', $invoice->rrr)->update($datas);
        }
        // New students enter this payment page with a temporary `payment`
        // session. Promote it to the normal portal session after settlement;
        // otherwise /payment sends them back to the login page.
        session()->put('log', '1');
        session()->forget('payment');
        echo '<script>window.location.href=' . json_encode($destination) . ';</script>';
    } else {
        session()->put('payment', '1');
        echo '<script>window.location.href="/school-fees";</script>';
    }
} else {
    session()->put('payment', '1');
    echo '<script>window.location.href="/school-fees";</script>';
}
?>
