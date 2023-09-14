<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

//use Illuminate\Support\Facades\Crypt;

class SiteSettingController extends Controller
{
    public function createOne(Request $request): JsonResponse
    {
        $data = $request->all();
        try {
            $validator = Validator::make($data, [
                'key_setting' => 'bail|required|unique:site_settings|regex:/^[a-zA-Z_]{2,30}$/',
            ], [
                'key_setting.required' => 'Key không được để trống.',
                'key_setting.unique' => 'Key đã tồn tại.',
                'key_setting.regex' => 'Key chỉ bao gồm chữ cái và _.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }
            SiteSetting::create([
                'key_setting' => $data['key_setting']
            ]);
            return response()->json(['status' => 'ok', 'message' => 'Tạo Key Setting thành công!']);
        } catch
        (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function updateSetting(Request $request): JsonResponse
    {
        $data = $request->all();
        $errors = [];
        try {
            foreach ($data as $key => $value) {
                $siteSetting = SiteSetting::where('key_setting', $key)->first();
                if (!$siteSetting) {
                    $errors[] = 'Không tìm thấy khóa: '.$key;
                }
            }
            if (count($errors) > 0) {
                $errorMessage = implode("\n", $errors);
                return response()->json(['status' => 'error', 'message' => $errorMessage]);
            }
            foreach ($data as $key => $value) {
                $siteSetting = SiteSetting::where('key_setting', $key)->first();
                if (in_array($key, ['logo_bg', 'logo_wh', 'favicon', 'logo_mini', 'meta_tag_social_img'])) {
                    if (File::exists($siteSetting->value_setting)) {
                        File::delete($siteSetting->value_setting);
                    }
//                  Decode Image
                    $image_64 = $value;
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $imageName = $key.'_'.time().'.'.$extension;
                    $storagePath = public_path('images/'.$imageName);
                    file_put_contents($storagePath, base64_decode($image));
//                        Storage::disk('public')->put($imageName, base64_decode($image));
                    $siteSetting->value_setting = 'images/'.$imageName;
                } else {
                    $siteSetting->value_setting = $value;
                }
                $siteSetting->save();
            }
            $siteSetting = SiteSetting::where('key_setting', 'setting_ver')->first();
            $siteSetting->value_setting = time();
            $siteSetting->save();
            return response()->json(['status' => 'ok', 'message' => 'Lưu dữ liệu thành công!']);
        } catch
        (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function getSecretKey(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (Hash::check($request->input('password'), $user->password)) {
                $secretKey = SiteSetting::where('key_setting', 'secret_key')->pluck('value_setting')->first();
                return response()->json(['status' => 'ok', 'message' => 'Xác nhận thành công!', 'data' => $secretKey]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Sai mật khẩu vui long thử lại!']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }

    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function newSecretKey(): JsonResponse
    {
        try {
            $newKey = hash('sha256', time());
            $secretKey = SiteSetting::where('key_setting', 'secret_key')->first();
            $secretKey->value_setting = $newKey;
            $secretKey->save();
            return response()->json(['status' => 'ok', 'message' => 'Tạo mã mới thành công!', 'data' => $newKey]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }

    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function newIdApp(): JsonResponse
    {
        try {
            $newID = time();
            $idApp = SiteSetting::where('key_setting', 'id_app')->first();
            $idApp->value_setting = $newID;
            $idApp->save();
            return response()->json(['status' => 'ok', 'message' => 'Cấp mới ID App thành công!', 'data' => $newID]);
//            $payload = Crypt::encrypt($newID);
//            return response()->json(['status' => 'ok', 'message' => 'Cấp mới ID App thành công!', 'data' => $payload,'encrypted'=>true]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }

    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//


    public function fetchGSetting(): JsonResponse
    {
        try {
            $siteName = SiteSetting::where('key_setting', 'site_name')->pluck('value_setting')->first();
            $logoBG = SiteSetting::where('key_setting', 'logo_bg')->pluck('value_setting')->first();
            $logoWH = SiteSetting::where('key_setting', 'logo_wh')->pluck('value_setting')->first();
            $logoMini = SiteSetting::where('key_setting', 'logo_mini')->pluck('value_setting')->first();
            $favicon = SiteSetting::where('key_setting', 'favicon')->pluck('value_setting')->first();
            $timeZone = SiteSetting::where('key_setting', 'time_zone')->pluck('value_setting')->first();
            $langCode = SiteSetting::where('key_setting', 'lang_code')->pluck('value_setting')->first();
            $mainColor = SiteSetting::where('key_setting', 'main_color')->pluck('value_setting')->first();
            $mainFont = SiteSetting::where('key_setting', 'main_font')->pluck('value_setting')->first();
            $idApp = SiteSetting::where('key_setting', 'id_app')->pluck('value_setting')->first();
            return response()->json([
                'siteName' => $siteName,
                'logoBG' => $logoBG,
                'logoWH' => $logoWH,
                'logoMini' => $logoMini,
                'favicon' => $favicon,
                'timeZone' => $timeZone,
                'langCode' => $langCode,
                'mainColor' => $mainColor,
                'mainFont' => $mainFont,
                'idApp' => $idApp,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }


    }
//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function fetchMSetting(): JsonResponse
    {
        try {
            return response()->json([
                'hostSMTP' => SiteSetting::where('key_setting', 'host_smtp')->pluck('value_setting')->first(),
                'encryptSMTP' => SiteSetting::where('key_setting', 'encrypt_smtp')->pluck('value_setting')->first(),
                'portSMTP' => SiteSetting::where('key_setting', 'port_smtp')->pluck('value_setting')->first(),
                'userSMTP' => SiteSetting::where('key_setting', 'user_smtp')->pluck('value_setting')->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }


    }


//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function fetchSSetting(): JsonResponse
    {
        try {
            return response()->json([
                'address' => SiteSetting::where('key_setting', 'shop_address')->pluck('value_setting')->first(),
                'phone' => SiteSetting::where('key_setting', 'shop_phone')->pluck('value_setting')->first(),
                'email' => SiteSetting::where('key_setting', 'shop_email')->pluck('value_setting')->first(),
                'timeWork' => SiteSetting::where('key_setting', 'shop_timeWork')->pluck('value_setting')->first(),
                'cskh' => SiteSetting::where('key_setting', 'shop_cskh')->pluck('value_setting')->first(),
                'cskhkn' => SiteSetting::where('key_setting', 'shop_cskhkn')->pluck('value_setting')->first(),
                'cskhbh' => SiteSetting::where('key_setting', 'shop_cskhbh')->pluck('value_setting')->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }


    }


//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function fetchSEOSetting(): JsonResponse
    {
        try {
            return response()->json([
                'metaTagTitle' => SiteSetting::where('key_setting', 'meta_tag_title')->pluck('value_setting')->first(),
                'favicon' => SiteSetting::where('key_setting', 'favicon')->pluck('value_setting')->first(),
                'metaTagKeywords' => SiteSetting::where('key_setting',
                    'meta_tag_keywords')->pluck('value_setting')->first(),
                'metaTagDescription' => SiteSetting::where('key_setting',
                    'meta_tag_description')->pluck('value_setting')->first(),
                'metaTagSocialImg' => SiteSetting::where('key_setting',
                    'meta_tag_social_img')->pluck('value_setting')->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }


    }


//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function verSetting(): JsonResponse
    {
        try {
            return response()->json([
                'setting_ver' => SiteSetting::where('key_setting', 'setting_ver')->pluck('value_setting')->first()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }
    }


//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function fetchPublicSetting(): JsonResponse
    {
        try {
            return response()->json([
                'setting_ver' => SiteSetting::where('key_setting', 'setting_ver')->pluck('value_setting')->first(),
                'site_name' => SiteSetting::where('key_setting', 'site_name')->pluck('value_setting')->first(),
                'logo_bg' => SiteSetting::where('key_setting', 'logo_bg')->pluck('value_setting')->first(),
                'logo_wh' => SiteSetting::where('key_setting', 'logo_wh')->pluck('value_setting')->first(),
                'logo_mini' => SiteSetting::where('key_setting', 'logo_mini')->pluck('value_setting')->first(),
                'favicon' => SiteSetting::where('key_setting', 'favicon')->pluck('value_setting')->first(),
                'lang_code' => SiteSetting::where('key_setting', 'lang_code')->pluck('value_setting')->first(),
                'shop_address' => SiteSetting::where('key_setting', 'shop_address')->pluck('value_setting')->first(),
                'shop_phone' => SiteSetting::where('key_setting', 'shop_phone')->pluck('value_setting')->first(),
                'shop_email' => SiteSetting::where('key_setting', 'shop_email')->pluck('value_setting')->first(),
                'shop_timeWork' => SiteSetting::where('key_setting', 'shop_timeWork')->pluck('value_setting')->first(),
                'shop_cskh' => SiteSetting::where('key_setting', 'shop_cskh')->pluck('value_setting')->first(),
                'shop_cskhkn' => SiteSetting::where('key_setting', 'shop_cskhkn')->pluck('value_setting')->first(),
                'shop_cskhbh' => SiteSetting::where('key_setting', 'shop_cskhbh')->pluck('value_setting')->first(),
                'meta_tag_title' => SiteSetting::where('key_setting',
                    'meta_tag_title')->pluck('value_setting')->first(),
                'meta_tag_keywords' => SiteSetting::where('key_setting',
                    'meta_tag_keywords')->pluck('value_setting')->first(),
                'meta_tag_description' => SiteSetting::where('key_setting',
                    'meta_tag_description')->pluck('value_setting')->first(),
                'meta_tag_social_img' => SiteSetting::where('key_setting',
                    'meta_tag_social_img')->pluck('value_setting')->first(),
                'main_color' => SiteSetting::where('key_setting', 'main_color')->pluck('value_setting')->first(),
                'main_font' => SiteSetting::where('key_setting', 'main_font')->pluck('value_setting')->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }

    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//


    public function fetchDSetting(): JsonResponse
    {
        try {
            return response()->json([
                'custom_Shipping' => SiteSetting::where('key_setting',
                    'custom_Shipping')->pluck('value_setting')->first(),
                'api_ghtk' => SiteSetting::where('key_setting', 'api_ghtk')->pluck('value_setting')->first(),
                'api_ghn' => SiteSetting::where('key_setting', 'api_ghn')->pluck('value_setting')->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }


    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function fetchPublicDSetting(): JsonResponse
    {
        try {
            return response()->json([
                'custom_Shipping' => SiteSetting::where('key_setting',
                    'custom_Shipping')->pluck('value_setting')->first(),
                'api_ghtk' => SiteSetting::where('key_setting', 'api_ghtk')->pluck('value_setting')->first() !== null,
                'api_ghn' => SiteSetting::where('key_setting', 'api_ghn')->pluck('value_setting')->first() !== null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }


    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function fetchBadWords(): JsonResponse
    {
        try {
            return response()->json([
                'bad_words' => SiteSetting::where('key_setting', 'bad_words')->pluck('value_setting')->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }


    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//


////////////////////////////////////////////////////////////////////////////////////////////////////////
////
////////////////////////////////////////////////////////////////////////////////////////////////////////
//////                            END FILE
////////////////////////////////////////////////////////////////////////////////////////////////////////
//////
////////////////////////////////////////////////////////////////////////////////////////////////////////
////
}
