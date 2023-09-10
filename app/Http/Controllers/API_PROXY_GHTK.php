<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class API_PROXY_GHTK extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        $curl = curl_init();
//dd(http_build_query($data));
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://services.giaohangtietkiem.vn/services/shipment/fee?".http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "Token: ".json_decode(SiteSetting::where('key_setting',
                    'api_ghtk')->pluck('value_setting')->first())->key,
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
//        dd($response->data);
        return response()->json(['success' => json_decode($response)->success, 'data' => json_decode($response)->fee->fee??null]);
    }
}
