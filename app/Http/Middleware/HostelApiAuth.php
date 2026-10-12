<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HostelApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $validApiKey = config('app.hostel_api_key', env('HOSTEL_API_KEY'));

        if (!$validApiKey || !$apiKey || !hash_equals($validApiKey, $apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid API Key',
            ], 401);
        }

        $timestamp = (string) $request->header('X-API-Timestamp', '');
        $signature = (string) $request->header('X-API-Signature', '');
        if ($timestamp === '' || !ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300 || $signature === '') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid request signature',
            ], 401);
        }

        $payload = $request->isMethod('get') ? $request->query() : $request->all();
        unset($payload['api_key']);
        ksort($payload);
        $path = $request->getPathInfo() ?: '/';
        $canonical = strtoupper($request->getMethod()) . "\n" . $path . "\n"
            . http_build_query($payload, '', '&', PHP_QUERY_RFC3986) . "\n" . $timestamp;
        $expected = hash_hmac('sha256', $canonical, $validApiKey);

        if (!hash_equals($expected, $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid request signature',
            ], 401);
        }

        return $next($request);
    }
}
