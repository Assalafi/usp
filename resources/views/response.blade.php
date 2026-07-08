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
        CURLOPT_URL => env('REMITA_CURLOPT_URL') . $merchantId . '/' . $rrr . '/' . $apiHash . '/status.reg',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $apiHash . ''],
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    $obj = json_decode($response, true);

    $status = $obj['status'];
    $amount = $obj['amount'];
    $paymentDate = $obj['paymentDate'];
    $transactiontime = $obj['transactiontime'];
    $paymentstatus = $obj['message'];

    if ($status == '021') {
        echo '<script>window.location.href="/payment";</script>';
    } elseif ($status == '023') {
        echo '<script>window.location.href="/payment";</script>';
    } elseif ($status == '01') {
        $datas['status'] = 'Paid';
        $datas['updated_at'] = NOW();
        $des = DB::table('invoices')
            ->where(['rrr' => $rrr, 'username' => session('id'), 'status' => 'Pending'])
            ->select('description', 'username', 'rrr')
            ->get();
        foreach ($des as $des) {
            if ($des->description == 'HOSTEL-MAINTENANCE/FEES') {
                $username = DB::table('students')->where('user_id', $des->username)->select('username')->value('username');
                $datass['hostel_payment'] = '1';
                DB::table('hostel')->where('occupant', $username)->update($datass);
            } elseif ($des->description == 'UNIVERSITY OF MAIDUGURI-1000127 FEES') {
                $session = DB::table('session')->where('status', '1')->value('title');
                $flag = 0;
                $noo = Student::where(['user_id' => session('id'), 'session_of_entry' => $session])
                    ->select('id_no')
                    ->value('id_no');
                if ($noo == 0) {
                    //while($flag == 0){
                    $data = Student::where(['user_id' => session('id'), 'session_of_entry' => '2023/2024'])
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
                            Student::where(['user_id' => session('id')])
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
            }

            DB::table('invoices')->where('rrr', $des->rrr)->update($datas);
        }
        echo '<script>window.location.href="/payment";</script>';
    } else {
        echo '<script>window.location.href="/payment";</script>';
    }
    echo '<script>window.location.href="/payment";</script>';
} else {
    echo '<script>window.location.href="/payment";</script>';
}
?>
