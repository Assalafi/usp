<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RecruitmentAccessController extends Controller
{
    private function getApiKey()
    {
        return config('app.recruitment_api_key', env('RECRUITMENT_API_KEY'));
    }

    private function apiCall($method, $endpoint, $data = [])
    {
        $apiUrl = 'https://employee.umstad.online/api/management/' . $endpoint;
        $apiKey = $this->getApiKey();

        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Accept' => 'application/json',
        ])->withoutVerifying()->timeout(30)->$method($apiUrl, $data);

        return $response->json();
    }

    public function index(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $data['page'] = 'recruitment-management';
        $data['title'] = 'RECRUITMENT MANAGEMENT';
        $data['statistics'] = $this->apiCall('get', 'statistics')['data'] ?? [];

        $access = DB::table('recruitment_access')->orderBy('name', 'ASC')->get();
        $data['accessList'] = $access;

        return view('main', $data);
    }

    public function store(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'username' => 'required|string',
        ]);

        $username = strtoupper(trim($request->username));
        $user = DB::table('users')->where('username', $username)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        DB::table('recruitment_access')->updateOrInsert(
            ['username' => $username],
            [
                'name' => $user->name ?? $username,
                'can_access' => $request->boolean('can_access'),
                'can_export' => $request->boolean('can_export'),
                'can_view_cv' => $request->boolean('can_view_cv'),
                'departments' => json_encode($request->input('departments', [])),
                'posts' => json_encode($request->input('posts', [])),
                'updated_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Access saved successfully']);
    }

    public function update(Request $request, $id)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $access = DB::table('recruitment_access')->where('id', $id)->first();
        if (!$access) {
            return response()->json(['success' => false, 'message' => 'Access record not found'], 404);
        }

        DB::table('recruitment_access')->where('id', $id)->update([
            'can_access' => $request->boolean('can_access'),
            'can_export' => $request->boolean('can_export'),
            'can_view_cv' => $request->boolean('can_view_cv'),
            'departments' => json_encode($request->input('departments', [])),
            'posts' => json_encode($request->input('posts', [])),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Access updated successfully']);
    }

    public function destroy($id)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        DB::table('recruitment_access')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Access removed successfully']);
    }

    public function users(Request $request)
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $q = trim($request->input('q', ''));
        $query = DB::table('users')
            ->whereIn('accType', ['Staff', 'Admin'])
            ->where('status', '1')
            ->select('username', 'name');

        if (strlen($q) >= 2) {
            $query->where(function ($sub) use ($q) {
                $sub->where('username', 'LIKE', "%{$q}%")
                    ->orWhere('name', 'LIKE', "%{$q}%");
            });
        }

        $users = $query->orderBy('name')->limit(20)->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function departments()
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $res = $this->apiCall('get', 'departments');
        $departments = collect($res['data'] ?? [])->pluck('name')->filter()->unique()->sort()->values();

        return response()->json(['success' => true, 'data' => $departments]);
    }

    public function posts()
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $res = $this->apiCall('get', 'jobs');
        $jobs = collect($res['data'] ?? [])->pluck('title')->filter()->unique()->sort()->values();

        return response()->json(['success' => true, 'data' => $jobs]);
    }
}
