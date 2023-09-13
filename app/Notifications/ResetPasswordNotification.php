<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $params_url = "";
        $params = [
            'email' => $notifiable->email,
            'token'=> $this->token,
            'exp'=> now()->addMinutes(config('auth.verification.expire', 60))
        ];
        foreach ($params as $key => $param){
            $params_url .="{$key}={$param}&";
        }
        $url = Config::get('app.url').'/reset-password?key='.Crypt::encryptString($params_url);
        return (new MailMessage)
            ->line('Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
            ->action('Đặt lại mật khẩu', $url)
            ->line(Lang::get('Liên kết đặt lại mật khẩu này sẽ hết hạn sau :count phút.',
                ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Nếu bạn không yêu cầu đặt lại mật khẩu thì không cần thực hiện thêm hành động nào.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
