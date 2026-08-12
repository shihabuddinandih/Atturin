<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRegistrationConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $eventId,
        public int $playerId,
        public string $registrationToken,
    ) {
        // job payload
    }

    public function handle()
    {
        Log::info('SendRegistrationConfirmationJob: registration link ready', [
            'event_id' => $this->eventId,
            'player_id' => $this->playerId,
            'registration_token' => $this->registrationToken,
        ]);

        // NOTE: WA sending/integration intentionally omitted here, same as
        // App\Jobs\SendWaitlistOfferJob. Integrate your WA provider (Fonnte/Wablas/
        // WA Business API) to send the registration confirmation message with the
        // link route('registration.show', $this->registrationToken) to the
        // player's contact number here.
    }
}
