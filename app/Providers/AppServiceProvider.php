<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

//      Update App config
        Config::set('app.name',SiteSetting::where('key_setting', 'site_name')->pluck('value_setting')->first());
        Config::set('app.vue_api_key',SiteSetting::where('key_setting', 'key_value_app')->pluck('value_setting')->first());
        Config::set('app.timezone',SiteSetting::where('key_setting', 'time_zone')->pluck('value_setting')->first());
        Config::set('mail.mailers.smtp.host',SiteSetting::where('key_setting', 'host_smtp')->pluck('value_setting')->first());
        Config::set('mail.mailers.smtp.port',SiteSetting::where('key_setting', 'port_smtp')->pluck('value_setting')->first());
        Config::set('mail.mailers.smtp.encryption',SiteSetting::where('key_setting', 'encrypt_smtp')->pluck('value_setting')->first());
        Config::set('mail.mailers.smtp.username',SiteSetting::where('key_setting', 'user_smtp')->pluck('value_setting')->first());
        Config::set('mail.mailers.smtp.password',SiteSetting::where('key_setting', 'password_smtp')->pluck('value_setting')->first());
        Config::set('mail.from.address',SiteSetting::where('key_setting', 'user_smtp')->pluck('value_setting')->first());
        Config::set('mail.from.name',SiteSetting::where('key_setting', 'site_name')->pluck('value_setting')->first());
    }
}
