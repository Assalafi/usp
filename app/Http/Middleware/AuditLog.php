<?php

namespace App\Http\Middleware;

use App\Models\Audit;
use Closure;
use Illuminate\Http\Request;

class AuditLog
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $page = trim($request->path(), '/');

            // Capture POST payload (exclude sensitive fields)
            $payload = null;
            if ($request->isMethod('post')) {
                $data = $request->except(['_token', 'password', 'password_confirmation', 'file']);
                $payload = !empty($data) ? json_encode($data) : null;
            }

            Audit::create([
                'username' => session('username'),
                'acc_type' => session('accType'),
                'appointment' => session('appointment'),
                'page' => $page,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $payload,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Audit log failed: ' . $e->getMessage());
        }

        return $response;
    }
}
