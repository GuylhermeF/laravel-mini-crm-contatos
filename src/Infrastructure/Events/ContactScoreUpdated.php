<?php

declare(strict_types=1);

namespace Infrastructure\Events;

use Domain\Contact\Entities\Contact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ContactScoreUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $contactId,
        public readonly string $name,
        public readonly string $email,
        public readonly int $score,
        public readonly string $status,
        public readonly ?string $processedAt,
    ) {
    }

    public static function fromContact(Contact $contact): self
    {
        return new self(
            contactId: $contact->id(),
            name: $contact->name()->value(),
            email: $contact->email()->value(),
            score: $contact->score()->value(),
            status: $contact->status()->value,
            processedAt: $contact->processedAt()?->format('Y-m-d H:i:s'),
        );
    }

    public function broadcastOn(): Channel
    {
        return new Channel("contacts.{$this->contactId}");
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'contact' => [
                'id' => $this->contactId,
                'name' => $this->name,
                'email' => $this->email,
                'score' => $this->score,
                'status' => $this->status,
                'processed_at' => $this->processedAt,
            ],
        ];
    }
}
