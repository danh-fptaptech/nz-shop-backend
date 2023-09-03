<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
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
//        dd(SiteSetting::where('key_setting', 'key_name_app')->pluck('value_setting')->first());

        $apiKey = $request->header(SiteSetting::where('key_setting', 'key_name_app')->pluck('value_setting')->first()??'X-Vue-Api-Key');
        $validApiKey = SiteSetting::where('key_setting', 'key_value_app')->pluck('value_setting')->first()??config('app.vue_api_key');
        $idApp = $request->header(SiteSetting::where('key_setting', 'id_app')->pluck('value_setting')->first());
        $secretKey = SiteSetting::where('key_setting', 'secret_key')->pluck('value_setting')->first();
//        dd($secretKey);
        if ($apiKey !== $validApiKey && $idApp !== $secretKey) {
            return response()->json(['error' => 'Invalid API Key'], 401);
        }
        return $next($request);
    }
}
