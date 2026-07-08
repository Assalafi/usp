<?php
// app\Http\Middleware\ThrottleRequests.php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottleRequests;

class ThrottleRequests extends BaseThrottleRequests
{
    /**
     * Get the rate limiter for the given key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return \Illuminate\Cache\RateLimiter
     */
    protected function getRateLimiter($request, $maxAttempts, $decayMinutes)
    {
        return parent::getRateLimiter($request, $maxAttempts, $decayMinutes);
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . $request->path() .
            '|' . $request->ip()
        );
    }
}
