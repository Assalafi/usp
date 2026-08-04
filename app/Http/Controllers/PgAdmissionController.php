<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PgAdmissionController extends Controller
{
    private function getApiUrl()
    {
        return config('app.pg_admission_api_url', env('PG_ADMISSION_API_URL', 'https://pg.umstad.online/api/v1/pg-admissions'));
    }

    private function getApiKey()
    {
        return config('app.pg_admission_api_key', env('PG_ADMISSION_API_KEY'));
    }

    private function apiCall($method, $endpoint, $data = [])
    {
        $baseUrl = $this->getApiUrl();
        $apiKey = $this->getApiKey();

        $url = $baseUrl . '/' . $endpoint;
        $data['api_key'] = $apiKey;

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(30)->$method($url, $data);

            return $response->json();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'PG School API unreachable: ' . $e->getMessage(),
            ];
        }
    }

    public function index()
    {
        if (!session()->has('log') || (session('accType') != 'Admin' && session('appointment') != 'VC')) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $overview = $this->apiCall('get', 'overview');
        $data['page'] = 'pg-admission';
        $data['title'] = 'PG SCHOOL ADMISSION';
        $data['overview'] = $overview['data'] ?? [];

        return view('main', $data);
    }

    public function overview()
    {
        if (!session()->has('log') || (session('accType') != 'Admin' && session('appointment') != 'VC')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json($this->apiCall('get', 'overview'));
    }

    public function applications(Request $request)
    {
        if (!session()->has('log') || (session('accType') != 'Admin' && session('appointment') != 'VC')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $params = array_filter([
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'program_id' => $request->input('program_id'),
            'academic_session_id' => $request->input('academic_session_id'),
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 50),
        ]);

        return response()->json($this->apiCall('get', 'applications', $params));
    }

    public function bulkApprove(Request $request)
    {
        if (!session()->has('log') || (session('accType') != 'Admin' && session('appointment') != 'VC')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'string',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $username = session('username');
        $name = \Illuminate\Support\Facades\DB::table('users')->where('username', $username)->value('name');
        if (!$name) {
            $name = \Illuminate\Support\Facades\DB::table('staff')->where('username', $username)->value('name');
        }
        $approvedBy = trim(($name ? $name . ' ' : '') . '(' . $username . ')');

        return response()->json($this->apiCall('post', 'bulk-approve', [
            'application_ids' => $request->application_ids,
            'remarks' => $request->remarks,
            'approved_by' => $approvedBy,
        ]));
    }

    public function history(Request $request)
    {
        if (!session()->has('log') || (session('accType') != 'Admin' && session('appointment') != 'VC')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $params = array_filter([
            'search' => $request->input('search'),
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 50),
        ]);

        return response()->json($this->apiCall('get', 'history', $params));
    }
}
