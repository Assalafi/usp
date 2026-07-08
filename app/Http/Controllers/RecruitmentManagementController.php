<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecruitmentManagementController extends Controller
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

    public function index()
    {
        if (!session()->has('log') || session('accType') != 'Admin') {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Get statistics
        $stats = $this->apiCall('get', 'statistics');
        $statistics = $stats['data'] ?? [];

        $data['page'] = 'recruitment-management';
        $data['title'] = 'RECRUITMENT MANAGEMENT';
        $data['statistics'] = $statistics;

        return view('main', $data);
    }
}
