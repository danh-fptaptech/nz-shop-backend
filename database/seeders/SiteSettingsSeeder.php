<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        Cách 1
        SiteSetting::create([
            'key_setting' => 'site_name',
            'value_setting' => 'NZ Shop Project'
        ]);
//        Cách 2
        $settings = [
            [
                'key_setting' => 'logo_bg',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'logo_wh',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'logo_mini',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'favicon',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'time_zone',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'lang_code',
                'value_setting' => 'en'
            ],
            [
                'key_setting' => 'id_app',
                'value_setting' => '123'
            ],
            [
                'key_setting' => 'secret_key',
                'value_setting' => '123'
            ],
            [
                'key_setting' => 'host_smtp',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'encrypt_smtp',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'port_smtp',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'user_smtp',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'password_smtp',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'shop_address',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'shop_phone',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'shop_email',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'shop_timeWork',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'shop_cskh',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'shop_cskhkn',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'shop_cskhbh',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'meta_tag_title',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'meta_tag_keywords',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'meta_tag_description',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'meta_tag_social_img',
                'value_setting' => ''
            ],
            [
                'key_setting' => 'setting_ver',
                'value_setting' => ''
            ],
        ];
        SiteSetting::insert($settings);
    }
}
