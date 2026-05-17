<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Enums\ContactStatus;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use Domain\Contact\ValueObjects\Score;
use Infrastructure\Persistence\Eloquent\Models\ContactModel;

final class EloquentContactRepository implements ContactRepositoryInterface
{
    public function findById(int $id): ?Contact
    {
        $model = ContactModel::find($id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByEmail(string $email): ?Contact
    {
        $model = ContactModel::where('email', strtolower($email))->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(Contact $contact): Contact
    {
        if ($contact->id() === null) {
            $model = ContactModel::create($this->toArray($contact));
        } else {
            $model = ContactModel::findOrFail($contact->id());
            $model->update($this->toArray($contact));
            $model->refresh();
        }

        return $this->toDomain($model);
    }

    public function delete(int $id): void
    {
        ContactModel::findOrFail($id)->delete();
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $paginator = ContactModel::paginate(
            perPage: $perPage,
            page: $page,
        );

        return [
            'data' => collect($paginator->items())
                ->map(fn (ContactModel $model) => $this->toDomain($model))
                ->toArray(),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    private function toDomain(ContactModel $model): Contact
    {
        return Contact::reconstitute(
            id: $model->id,
            name: new ContactName($model->name),
            email: new Email($model->email),
            phone: new Phone($model->phone),
            score: new Score($model->score),
            status: ContactStatus::from($model->status),
            processedAt: $model->processed_at
                ? new DateTimeImmutable($model->processed_at->toIso8601String())
                : null,
        );
    }

    private function toArray(Contact $contact): array
    {
        return [
            'name' => $contact->name()->value(),
            'email' => $contact->email()->value(),
            'phone' => $contact->phone()->value(),
            'score' => $contact->score()->value(),
            'status' => $contact->status()->value,
            'processed_at' => $contact->processedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
