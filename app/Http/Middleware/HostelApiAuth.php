<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HostelApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?: $request->query('api_key');
        $validApiKey = config('app.hostel_api_key', env('HOSTEL_API_KEY'));

        if (!$validApiKey || !$apiKey || !hash_equals($validApiKey, $apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid API Key',
            ], 401);
        }

        return $next($request);
    }
}
