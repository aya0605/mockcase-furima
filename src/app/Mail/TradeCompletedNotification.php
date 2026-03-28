<?php

namespace App\Mail;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TradeCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('【通知】取引が完了しました')
                    ->view('emails.trade_completed');
    }
}
