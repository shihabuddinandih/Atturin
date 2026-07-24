<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventWaitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WaitlistReminderAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public EventWaitlist $waitlist;
    public Event $event;
    public string $playerName;
    public float|int $paymentAmount;
    public string $paymentLink;
    public string $message;

    public function __construct(EventWaitlist $waitlist, string $playerName, float|int $paymentAmount, string $paymentLink, string $message)
    {
        $this->waitlist = $waitlist;
        $this->event = $waitlist->event;
        $this->playerName = $playerName;
        $this->paymentAmount = $paymentAmount;
        $this->paymentLink = $paymentLink;
        $this->message = $message;
    }

    public function build(): self
    {
        return $this->subject('Reminder waitlist pemain perlu ditindaklanjuti')
            ->view('emails.waitlist-reminder-alert');
    }
}
