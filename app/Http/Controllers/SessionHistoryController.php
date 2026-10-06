<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SessionHistory;

class SessionHistoryController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        
        $username = $request->username;
        
        // Only query when a username is provided
        if ($username) {
            $data = DB::table('session_history')
                ->select('*')
                ->where('username', 'like', '%' . $username . '%')
                ->orderBy('id', 'desc')
                ->paginate(15)
                ->appends(['username' => $username]); // Keep filter when paginating
        } else {
            // Return empty paginated results
            $data = DB::table('session_history')
                ->select('*')
                ->whereRaw('1 = 0') // This will always be false, returning no results
                ->orderBy('id', 'desc')
                ->paginate(15);
        }
        
        // Get faculty and session data for the generate modal
        $faculty = DB::table('faculty')->orderBy('title')->get();
        $session = DB::table('session')->orderBy('title', 'desc')->get();
            
        return view('main', [
            'page' => 'session history', 
            'data' => $data,
            'username' => $username,
            'faculty' => $faculty,
            'session' => $session
        ]);
    }
    
    public function create(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        
        $validated = $request->validate([
            'username' => 'required',
            'session' => 'required',
            'level' => 'required',
            'total_unit' => 'nullable|numeric',
            'product' => 'nullable|numeric',
            'cgpa' => 'nullable|numeric',
            'status' => 'nullable',
        ]);
        
        DB::table('session_history')->insert([
            'username' => $request->username,
            'session' => $request->session,
            'level' => $request->level,
            'total_unit' => $request->total_unit,
            'product' => $request->product,
            'cgpa' => $request->cgpa,
            'status' => $request->status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Session history record added successfully');
    }
    
    public function update(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        
        $validated = $request->validate([
            'id' => 'required',
            'username' => 'required',
            'session' => 'required',
            'level' => 'required',
            'total_unit' => 'nullable|numeric',
            'product' => 'nullable|numeric',
            'cgpa' => 'nullable|numeric',
            'status' => 'nullable',
        ]);
        
        DB::table('session_history')->where('id', $request->id)->update([
            'username' => $request->username,
            'session' => $request->session,
            'level' => $request->level,
            'total_unit' => $request->total_unit,
            'product' => $request->product,
            'cgpa' => $request->cgpa,
            'status' => $request->status,
            'updated_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Session history record updated successfully');
    }
    
    public function delete(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        
        if ($request->has('ids')) {
            // Convert comma-separated string to array if needed
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            DB::table('session_history')->whereIn('id', $ids)->delete();
            return redirect()->back()->with('success', 'Selected session history records deleted successfully');
        } else {
            DB::table('session_history')->where('id', $request->id)->delete();
            return redirect()->back()->with('success', 'Session history record deleted successfully');
        }
    }
    
    public function upload(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            if ($extension != 'csv') {
                return redirect()->back()->with('error', 'Only CSV files are allowed');
            }
            
            $fileContents = file($file->getPathname());
            
            // Skip the first line (headers)
            unset($fileContents[0]);
            
            foreach ($fileContents as $line) {
                $data = str_getcsv($line);
                
                if (count($data) >= 7) {
                    DB::table('session_history')->insert([
                        'username' => $data[0],
                        'session' => $data[1],
                        'level' => $data[2],
                        'total_unit' => $data[3] ?: null,
                        'product' => $data[4] ?: null,
                        'cgpa' => $data[5] ?: null,
                        'status' => $data[6] ?: null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            return redirect()->back()->with('success', 'Session history records imported successfully');
        }
        
        return redirect()->back()->with('error', 'No file selected');
    }
    
    public function generateFromEntry(Request $request)
    {
        if (!session()->has('log')) {
            return redirect('/');
        }

        $targetSession = trim((string) $request->input('session'));
        $faculty = $request->input('faculty');
        $department = $request->input('department');
        $program = $request->input('program');
        $offset = max(0, (int) $request->input('offset', 0));
        $createdTotal = max(0, (int) $request->input('created_total', 0));
        $preservedTotal = max(0, (int) $request->input('preserved_total', 0));
        $limit = 100;

        if (!$targetSession) {
            return redirect()->back()->with('error', 'Session is required');
        }

        // Build a stable student query for the selected entry session.
        $query = DB::table('students')
            ->whereNotNull('session_of_entry')
            ->where('session_of_entry', $targetSession)
            ->where('id_no', '!=', 0)
            ->whereNotNull('id_no');

        // Apply optional filters (check for actual values, not placeholders).
        if (!empty($program) && $program !== 'all' && $program !== 'Select Department First') {
            $query->where('program', $program);
        }
        if (!empty($department) && $department !== 'all' && $department !== 'Select Faculty First') {
            $query->where('department', $department);
        }
        if (!empty($faculty) && $faculty !== 'all' && $faculty !== 'All Faculties') {
            $query->where('faculty', $faculty);
        }

        $totalStudents = (clone $query)->count();

        if ($totalStudents == 0) {
            return redirect()->back()->with('error', 'No students found with the selected criteria');
        }

        // Keep pagination stable while batches are being processed. Existing
        // rows are never deleted or overwritten; only missing rows are inserted.
        $students = $query->orderBy('students.id', 'asc')->skip($offset)->take($limit)->get();

        if ($students->isEmpty()) {
            return redirect('/session history')->with('success', "Session history check completed for {$totalStudents} students. {$createdTotal} missing record(s) created; {$preservedTotal} existing record(s) preserved.");
        }

        $usernames = $students->pluck('username')->filter()->values()->all();
        $existing = DB::table('session_history')
            ->where('session', $targetSession)
            ->whereIn('username', $usernames)
            ->pluck('username')
            ->flip();

        $rows = [];
        $preservedCount = 0;
        $timestamp = now();

        foreach ($students as $student) {
            if (!$student->username || isset($existing[$student->username])) {
                $preservedCount++;
                continue;
            }

            $rows[] = [
                'username' => $student->username,
                'session' => $targetSession,
                'level' => $student->level_of_entry ?? '100',
                'program' => $student->program,
                'total_unit' => 0,
                'product' => 0,
                'cgpa' => 0,
                'status' => 'PENDING',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        // insertOrIgnore makes a second/concurrent run safe when the unique
        // username + session key already exists.
        $createdCount = $rows
            ? DB::transaction(function () use ($rows) {
                return DB::table('session_history')->insertOrIgnore($rows);
            })
            : 0;
        $preservedCount += max(0, count($rows) - $createdCount);
        $createdTotal += $createdCount;
        $preservedTotal += $preservedCount;

        $newOffset = $offset + $students->count();
        $processed = min($newOffset, $totalStudents);
        $remaining = $totalStudents - $processed;

        $params = http_build_query([
            'session' => $targetSession,
            'faculty' => $faculty,
            'department' => $department,
            'program' => $program,
            'offset' => $newOffset,
            'created_total' => $createdTotal,
            'preserved_total' => $preservedTotal,
        ]);

        return "
            <html>
            <head><title>Checking Session History...</title></head>
            <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                <h3>Checking Session History</h3>
                <p>Processed: {$processed} / {$totalStudents} students</p>
                <p>New records created in this batch: {$createdCount}</p>
                <p>Existing records preserved: {$preservedCount}</p>
                <p>Remaining: {$remaining}</p>
                <div style='width: 300px; margin: 20px auto; background: #eee; border-radius: 5px;'>
                    <div style='width: " . round(($processed / $totalStudents) * 100) . "%; background: #28a745; height: 20px; border-radius: 5px;'></div>
                </div>
                <p><small>Please wait...</small></p>
                <script>
                    setTimeout(function() {
                        window.location.href = '/generate-session-history?{$params}';
                    }, 500);
                </script>
            </body>
            </html>
        ";
    }
}
