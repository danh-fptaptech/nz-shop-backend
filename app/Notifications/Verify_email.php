<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Lang;

class Verify_email extends Notification
{
    use Queueable;


    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
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
            'id' => $notifiable->getKey(),
            'hash'=> sha1($notifiable->getEmailForVerification()),
            'exp'=> now()->addMinutes(config('auth.verification.expire', 60))
        ];
        foreach ($params as $key => $param){
            $params_url .="{$key}={$param}&";
        }
        $url= Config::get('app.url').'/verify-email?key='.Crypt::encryptString($params_url);
        return (new MailMessage)
            ->subject('Xác nhận địa chỉ email')
            ->line('Chào mừng bạn đã đến NzShop. Hãy xác nhận tại khoản bằng liên kết bên dưới để có thể sử dụng tài khoản đầy đủ các chức năng.')
            ->action('Xác nhận tài khoản', $url)
            ->line(Lang::get('Chúc bạn có một ngày vui vẻ.'));
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
