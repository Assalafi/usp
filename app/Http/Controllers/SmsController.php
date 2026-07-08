<?php

namespace App\Http\Controllers;

use App\Models\SentSms;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SmsController extends Controller
{
    public function create()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return view('main', ['page' => 'sms.create']);
    }

    public function send(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $request->validate([
            'subject' => 'nullable|string|max:255',
            'sender_name' => 'required|string|max:11',
            'recipients' => 'required|string',
            'message' => 'required|string',
        ]);

        $email = config('services.multitexter.email');
        $password = config('services.multitexter.password');
        $forcednd = '1';

        $data = [
            'email' => $email,
            'password' => $password,
            'message' => $request->message,
            'sender_name' => $request->sender_name,
            'recipients' => $request->recipients,
            'forcednd' => $forcednd
        ];

        // Log the message before sending
        $sentSms = SentSms::create([
            'username' => session('username'),
            'sender_name' => $request->sender_name,
            'subject' => $request->subject,
            'recipients' => $request->recipients,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://app.multitexter.com/v2/app/sms', $data);

            $result = $response->json();

            // Log for debugging
            Log::info('MultiTexter Response:', $result);

            if ($response->successful()) {
                // Handle the actual response format: {"status":1,"msgid":"msg_68cfd6c58ff51","units":12,"balance":"150.00","msg":"Message has been sent"}
                if (isset($result['status']) && $result['status'] == 1) {
                    $message = $result['msg'] ?? 'SMS sent successfully!';

                    // Add additional info
                    $info = [];
                    if (isset($result['units']))
                        $info[] = "Units used: {$result['units']}";
                    // if (isset($result['balance']))
                    //     $info[] = "Remaining balance: {$result['balance']}";
                    if (isset($result['msgid'])) {
                        $info[] = "Message ID: {$result['msgid']}";
                        $sentSms->update(['status' => 'success', 'msg_id' => $result['msgid']]);
                    } else {
                        $sentSms->update(['status' => 'success']);
                    }
                    if (!empty($info)) {
                        $message .= ' (' . implode(', ', $info) . ')';
                    }

                    return redirect()->back()->with('success', $message);
                } elseif (isset($result['status']) && $result['status'] == 0) {
                    // Error case
                    $errorMessage = $result['msg'] ?? 'Failed to send SMS';
                    $sentSms->update(['status' => 'failed']);
                    return redirect()->back()->with('error', $errorMessage);
                } else {
                    // Unknown status format
                    return redirect()->back()->with('error', 'Unknown API response: ' . json_encode($result));
                }
            } else {
                // Handle HTTP errors
                $errorMessage = 'Failed to send SMS. HTTP Error: ' . $response->status();
                if (isset($result['msg'])) {
                    $errorMessage .= ' - ' . $result['msg'];
                }
                $sentSms->update(['status' => 'failed']);
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('MultiTexter SMS Error: ' . $e->getMessage(), ['data' => $data]);
            $sentSms->update(['status' => 'failed']);
            return redirect()->back()->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function viewNonAcademicStaff()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $staffQuery = Staff::where('faculty', 'NON ACADEMIC')
            ->where('degree', '1')
            ->where(function ($query) {
                $query
                    ->whereRaw('LENGTH(phone) = 11 AND phone LIKE "0%"')
                    ->orWhereRaw('LENGTH(phone) = 13 AND phone LIKE "234%"');
            });

        $allStaff = $staffQuery->get();

        $formattedStaff = $allStaff->map(function ($member) {
            $phone = trim($member->phone);
            if (strlen($phone) == 11 && substr($phone, 0, 1) === '0') {
                // Convert 080... to 23480...
                $member->phone = '234' . substr($phone, 1);
            }
            // Numbers already in 234 format are left as is
            return $member;
        });

        // Manually paginate the formatted collection
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $currentPageItems = $formattedStaff->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedStaff = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems, count($formattedStaff), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('main', ['page' => 'sms.non_academic_staff', 'staff' => $paginatedStaff]);
    }

    public function showResetProgress()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return view('main', ['page' => 'sms.reset_progress']);
    }

    public function resetAndNotifyStream()
    {
        $response = new StreamedResponse(function () {
            set_time_limit(0);

            $sendMessage = function (string $event, array $data): void {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $staffQuery = Staff::where('faculty', 'NON ACADEMIC')
                    ->where('degree', '1')
                    ->where(function ($query) {
                        $query
                            ->whereRaw('LENGTH(phone) = 11 AND phone LIKE "0%"')
                            ->orWhereRaw('LENGTH(phone) = 13 AND phone LIKE "234%"');
                    });

                $totalCount = $staffQuery->count();
                $processedCount = 0;
                $successCount = 0;
                $failCount = 0;
                $skippedCount = 0;

                $sendMessage('status', [
                    'message' => "Found {$totalCount} staff records to process...",
                ]);

                if ($totalCount === 0) {
                    $sendMessage('finished', ['message' => 'No staff members met the criteria. Nothing to do.']);
                    return;
                }

                $staffQuery->chunk(20, function ($staffMembers) use ($sendMessage, &$processedCount, &$successCount, &$failCount, &$skippedCount, $totalCount) {
                    foreach ($staffMembers as $staff) {
                        DB::beginTransaction();
                        try {
                            // Check if the user has already been processed
                            if (SentSms::where('username', $staff->username)->exists()) {
                                $skippedCount++;
                                $sendMessage('progress', [
                                    'message' => "⏩ User '{$staff->username}' has already been processed. Skipping.",
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                                DB::rollBack();  // Nothing to do in this transaction
                                continue;
                            }

                            $phone = trim($staff->phone);
                            if (strlen($phone) == 11 && substr($phone, 0, 1) === '0') {
                                $phone = '234' . substr($phone, 1);
                            }

                            $user = User::where('username', $staff->username)->first();

                            if (!$user) {
                                $failCount++;
                                $sendMessage('progress', [
                                    'message' => "⚠️ Warning: Staff '{$staff->username}' has no matching user account. Skipping.",
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                                DB::rollBack();
                                continue;
                            }

                            $newPassword = Str::random(6);
                            $user->password = Hash::make($newPassword);
                            $user->save();

                            $message = "From UNIMAID Congregation, {$newPassword} is your password.";
                            $data = [
                                'email' => config('services.multitexter.email'),
                                'password' => config('services.multitexter.password'),
                                'message' => $message,
                                'sender_name' => 'UNIMAID',
                                'recipients' => $phone,
                                'forcednd' => '1'
                            ];

                            $smsResponse = Http::withOptions(['verify' => false])->post('https://app.multitexter.com/v2/app/sms', $data);
                            $smsResult = $smsResponse->json();

                            $smsStatus = ($smsResponse->successful() && isset($smsResult['status']) && $smsResult['status'] == 1) ? 'success' : 'failed';

                            SentSms::create([
                                'username' => $staff->username,
                                'sender_name' => 'UNIMAID',
                                'subject' => 'Password Reset',
                                'recipients' => $phone,
                                'message' => $message,
                                'status' => $smsStatus,
                                'msg_id' => $smsResult['msgid'] ?? null,
                            ]);

                            if ($smsStatus === 'success') {
                                $successCount++;
                                $sendMessage('progress', [
                                    'message' => "✅ Successfully reset password and sent SMS to {$user->username}.",
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                            } else {
                                $failCount++;
                                $sendMessage('progress', [
                                    'message' => "❌ Failed to send SMS to {$user->username}. Password was reset but not sent.",
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                            }
                            DB::commit();
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            Log::error("Failed to process staff ID {$staff->id}: " . $e->getMessage());
                            $failCount++;
                            $sendMessage('progress', [
                                'message' => "❌ Error processing staff '{$staff->username}': " . $e->getMessage() . '. Continuing...',
                                'progress' => round((++$processedCount / $totalCount) * 100),
                            ]);
                        }
                    }
                });

                $sendMessage('finished', [
                    'message' => "✅ Process completed! Success: {$successCount}, Failed: {$failCount}, Skipped: {$skippedCount}."
                ]);
            } catch (\Throwable $e) {
                Log::error('A critical error stopped the password reset process: ' . $e->getMessage());
                $sendMessage('finished', ['message' => '❌ A critical error occurred: ' . $e->getMessage()]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    public function resendSingle(SentSms $sentSms)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        try {
            $data = [
                'email'       => config('services.multitexter.email'),
                'password'    => config('services.multitexter.password'),
                'message'     => $sentSms->message,
                'sender_name' => $sentSms->sender_name,
                'recipients'  => $sentSms->recipients,
                'forcednd'    => '1',
            ];

            $response = Http::withOptions(['verify' => false])->post('https://app.multitexter.com/v2/app/sms', $data);
            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] == 1) {
                $sentSms->increment('sent');
                return redirect()->back()->with('success', 'SMS has been resent successfully.');
            } else {
                $errorMessage = $result['msg'] ?? 'Unknown error from SMS provider.';
                return redirect()->back()->with('error', 'Failed to resend SMS: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('SMS Resend Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while trying to resend the SMS.');
        }
    }

    public function showResendAllProgress()
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        return view('main', ['page' => 'sms.resend_all_progress']);
    }

    public function resendAllStream(Request $request)
    {
        $resendCount = $request->input('resend_count', 1);

        $response = new StreamedResponse(function () use ($request, $resendCount) {
            set_time_limit(0);

            $sendMessage = function (string $event, array $data): void {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $query = SentSms::latest();

                if ($request->filled('username')) {
                    $query->where('username', 'like', '%' . $request->username . '%');
                }

                // Only include records that have been sent less than the target number of times
                $query->where('sent', '<', $resendCount);

                $totalCount = $query->count();
                $processedCount = 0;
                $successCount = 0;
                $failCount = 0;

                $sendMessage('status', [
                    'message' => "Found {$totalCount} messages to resend...",
                ]);

                if ($totalCount === 0) {
                    $sendMessage('finished', ['message' => 'No messages matched the criteria. Nothing to do.']);
                    return;
                }

                $query->chunk(20, function ($messages) use ($sendMessage, &$processedCount, &$successCount, &$failCount, $totalCount, $resendCount) {
                    foreach ($messages as $sms) {
                        try {
                            $data = [
                                'email'       => config('services.multitexter.email'),
                                'password'    => config('services.multitexter.password'),
                                'message'     => $sms->message,
                                'sender_name' => $sms->sender_name,
                                'recipients'  => $sms->recipients,
                                'forcednd'    => '1',
                            ];

                            $smsResponse = Http::withOptions(['verify' => false])->post('https://app.multitexter.com/v2/app/sms', $data);
                            $smsResult = $smsResponse->json();

                            if ($smsResponse->successful() && isset($smsResult['status']) && $smsResult['status'] == 1) {
                                // Update the sent count on the original record
                                $sms->update(['sent' => $resendCount]);

                                $successCount++;
                                $sendMessage('progress', [
                                    'message' => "✅ Resent SMS to {$sms->username} (Attempt #{$resendCount}).",
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                            } else {
                                $failCount++;
                                $sendMessage('progress', [
                                    'message' => "❌ Failed to resend SMS to {$sms->username}: " . ($smsResult['msg'] ?? 'Unknown API error'),
                                    'progress' => round((++$processedCount / $totalCount) * 100),
                                ]);
                            }
                        } catch (\Throwable $e) {
                            Log::error("Failed to resend SMS ID {$sms->id}: " . $e->getMessage());
                            $failCount++;
                            $sendMessage('progress', [
                                'message' => "❌ Error resending to '{$sms->username}': " . $e->getMessage(),
                                'progress' => round((++$processedCount / $totalCount) * 100),
                            ]);
                        }
                    }
                });

                $sendMessage('finished', [
                    'message' => "✅ Process completed! Success: {$successCount}, Failed: {$failCount}."
                ]);
            } catch (\Throwable $e) {
                Log::error('A critical error stopped the bulk resend process: ' . $e->getMessage());
                $sendMessage('finished', ['message' => '❌ A critical error occurred: ' . $e->getMessage()]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    public function index(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $query = SentSms::latest();

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->filled('sent_status')) {
            if ($request->sent_status == 'failed') {
                $query->where('status', 'failed');
            }
        }

        $sentSms = $query->paginate(20)->withQueryString();

        return view('main', [
            'page' => 'sms.sent',
            'sentSms' => $sentSms,
            'filters' => $request->only(['username'])
        ]);
    }
}
