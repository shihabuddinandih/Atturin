<?php

namespace App\Jobs;

use App\Models\EventWaitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SendWaitlistOfferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public EventWaitlist $entry)
    {
        // job payload
    }

    public function handle()
    {
        // Mark as contacted and generate token + expiry window (24 hours)
        $token = Str::random(48);
        $updated = $this->entry->update([
            'status' => 'contacted',
            'token' => $token,
            'contacted_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        if ($updated) {
            Log::info('SendWaitlistOfferJob: entry contacted', ['entry_id' => $this->entry->id, 'token' => $token]);
        } else {
            Log::warning('SendWaitlistOfferJob: failed to update entry', ['entry_id' => $this->entry->id]);
        }

        // NOTE: WA sending/integration intentionally omitted here.
        // Integrate your WA provider (Bailey/Twilio/WA Business API) to send the message with claim link:
        // route('waitlist.claim', $this->entry->token)
    }
}
