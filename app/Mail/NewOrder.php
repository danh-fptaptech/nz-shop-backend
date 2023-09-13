<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrder extends Mailable
{
    use Queueable, SerializesModels;

    public $linkKey;

    /**
     * Create a new message instance.
     */
    public function __construct($linkKey)
    {
        $this->linkKey = $linkKey;
    }

    public function build(): NewOrder
    {
        return $this->markdown('emails.new.order')
            ->subject('Bạn vừa có đơn hàng mới');
    }
}
