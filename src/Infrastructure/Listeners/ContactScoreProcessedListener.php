<?php

declare(strict_types=1);

namespace Infrastructure\Listeners;

use Domain\Contact\Events\ContactScoreProcessed;
use Infrastructure\Events\ContactScoreUpdated;
use Illuminate\Support\Facades\Log;

final class ContactScoreProcessedListener
{
    public function handle(ContactScoreProcessed $event): void
    {
        $contact = $event->contact;

        // Log to storage/logs/contact.log
        Log::channel('contact')->info('Contact score processed', [
            'id' => $contact->id(),
            'email' => $contact->email()->value(),
            'score' => $contact->score()->value(),
            'status' => $contact->status()->value,
        ]);

        // Broadcast via Laravel Reverb / WebSocket
        broadcast(ContactScoreUpdated::fromContact($contact));
    }
}
