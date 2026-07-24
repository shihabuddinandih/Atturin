<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;
    public Player $player;
    public string $playerName;
    public float|int $paymentAmount;
    public string $paymentLink;
    public string $message;

    public function __construct(Event $event, Player $player, string $playerName, float|int $paymentAmount, string $paymentLink, string $message)
    {
        $this->event = $event;
        $this->player = $player;
        $this->playerName = $playerName;
        $this->paymentAmount = $paymentAmount;
        $this->paymentLink = $paymentLink;
        $this->message = $message;
    }

    public function build(): self
    {
        return $this->subject('Reminder pembayaran pendaftaran event')
            ->view('emails.payment-reminder-alert');
    }
}
