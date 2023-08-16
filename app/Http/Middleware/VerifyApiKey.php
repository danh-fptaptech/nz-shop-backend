<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//        Check header Vue-Api-Key
        $apiKey = $request->header('X-Vue-Api-Key');
        $validApiKey = config('app.vue_api_key');

        if ($apiKey !== $validApiKey) {
            return response()->json(['error' => 'Invalid API Key'], 401);
        }
        return $next($request);
    }
}
