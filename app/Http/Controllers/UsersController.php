<?php

namespace App\Http\Controllers;

use App\Imports\AssignImport;
use App\Imports\UsersImport;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //
        $data['page'] = 'users';
        return view('main', $data);
    }

    public function index2(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        //
        $data['page'] = 'users2';
        return view('main', $data);
    }

    public function uploadExcel(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Load the uploaded file using Maatwebsite/Excel
            $import = new UsersImport();
            Excel::import($import, $file);

            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function uploadExcel2(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Load the uploaded file using Maatwebsite/Excel
            $import = new AssignImport();
            Excel::import($import, $file);
            // Now, $data contains all the rows from the Excel file with non-empty emails
            return redirect()->back()->with('success', 'File imported successfully.');
        }

        return redirect()->back()->with('error', 'File not found or other error occurred.');
    }

    public function accountNumber()
    {
        // Use Laravel's StreamedResponse for Server-Sent Events
        $response = new StreamedResponse(function () {
            // --- FIX #1: REMOVE SCRIPT TIME LIMIT ---
            // This is crucial for long-running processes. 0 means unlimited.
            set_time_limit(0);

            // Helper function to format and send an SSE message
            $sendMessage = function (string $event, array $data): void {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $staffToProcess = Staff::query()
                    ->where('faculty', 'NON ACADEMIC')
                    ->whereNotNull('account_number')
                    ->whereRaw('LENGTH(TRIM(account_number)) = 10');

                $totalCount = $staffToProcess->count();
                $processedCount = 0;

                $sendMessage('status', [
                    'message' => "Found {$totalCount} staff records to process...",
                ]);

                if ($totalCount === 0) {
                    $sendMessage('finished', ['message' => 'No staff members met the criteria. Nothing to do.']);
                    return;
                }

                $staffToProcess->chunk(50, function ($staffMembers) use ($sendMessage, &$processedCount, $totalCount) {
                    foreach ($staffMembers as $staff) {
                        // --- FIX #2: ADD ERROR HANDLING FOR EACH RECORD ---
                        try {
                            $user = User::find($staff->user_id);

                            if ($user) {
                                $user->password = Hash::make(trim($staff->account_number));
                                $user->save();
                                $processedCount++;
                                $progress = round(($processedCount / $totalCount) * 100);
                                $sendMessage('progress', [
                                    'message' => "Updated password for user ID: {$user->username}",
                                    'progress' => $progress,
                                ]);
                            } else {
                                $sendMessage('progress', [
                                    'message' => "⚠️ Warning: Staff ID {$staff->id} has no matching user (user_id: {$staff->user_id}). Skipping.",
                                    'progress' => round(($processedCount / $totalCount) * 100),
                                ]);
                            }
                        } catch (Throwable $e) {
                            // If an error occurs on a single user, log it and continue
                            Log::error("Failed to process staff ID {$staff->id}: " . $e->getMessage());
                            $sendMessage('progress', [
                                'message' => "❌ Error processing staff ID {$staff->id}. Check server logs. Continuing...",
                                'progress' => round(($processedCount / $totalCount) * 100),
                            ]);
                        }
                    }
                });

                $sendMessage('finished', [
                    'message' => "✅ Success! Processed {$totalCount} records."
                ]);
            } catch (Throwable $e) {
                // This will catch errors in the initial query or setup
                Log::error('A critical error stopped the password update process: ' . $e->getMessage());
                $sendMessage('finished', [
                    'message' => '❌ Critical Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    public function accountNumberAsPasswordWithProgress()
    {
        // Use Laravel's StreamedResponse for Server-Sent Events
        $response = new StreamedResponse(function () {
            set_time_limit(0);

            // Helper function to format and send an SSE message
            $sendMessage = function (string $event, array $data): void {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $sendMessage('status', ['message' => 'Starting process...']);

                // Skip exclusion for now - just get all academic staff
                $staffToProcess = Staff::query()
                    ->where('staff.faculty', '!=', 'NON ACADEMIC')
                    ->join('users', 'users.id', '=', 'staff.user_id')
                    ->select('staff.id', 'staff.user_id', 'users.username')
                    ->orderBy('staff.id');

                $totalCount = $staffToProcess->count();
                $processedCount = 0;

                $sendMessage('status', ['message' => "Found {$totalCount} staff records to process..."]);

                if ($totalCount === 0) {
                    $sendMessage('finished', ['message' => 'No staff members found.']);
                    return;
                }

                $staffToProcess->chunk(50, function ($staffMembers) use ($sendMessage, &$processedCount, $totalCount) {
                    foreach ($staffMembers as $staff) {
                        try {
                            $user = User::find($staff->user_id);
                            if ($user) {
                                $user->password = Hash::make(\App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026'));
                                $user->save();
                                $processedCount++;
                                $progress = round(($processedCount / $totalCount) * 100);
                                $sendMessage('progress', [
                                    'message' => "Updated password for user: {$user->username}",
                                    'progress' => $progress,
                                ]);
                            }
                        } catch (Throwable $e) {
                            Log::error("Failed to process staff ID {$staff->id}: " . $e->getMessage());
                            $sendMessage('progress', [
                                'message' => "❌ Error processing staff ID {$staff->id}",
                                'progress' => round(($processedCount / $totalCount) * 100),
                            ]);
                        }
                    }
                });

                $sendMessage('finished', ['message' => "✅ Success! Processed {$totalCount} records."]);
            } catch (Throwable $e) {
                Log::error('Password update error: ' . $e->getMessage());
                $sendMessage('finished', [
                    'message' => '❌ Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
