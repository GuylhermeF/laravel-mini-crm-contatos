<?php

declare(strict_types=1);

namespace Infrastructure\Http\Resources;

use Domain\Contact\Entities\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contact
 */
final class ContactResource extends JsonResource
{
    public function __construct(private readonly Contact $contact)
    {
        parent::__construct($contact);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->contact->id(),
            'name' => $this->contact->name()->value(),
            'email' => $this->contact->email()->value(),
            'phone' => $this->contact->phone()->formatted(),
            'score' => $this->contact->score()->value(),
            'status' => $this->contact->status()->value,
            'status_label' => $this->contact->status()->label(),
            'processed_at' => $this->contact->processedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
