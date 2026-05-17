<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Contact\Events\ContactScoreProcessed;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Enums\ContactStatus;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use Domain\Contact\ValueObjects\Score;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class ContactScoreProcessedListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_logs_contact_score_event(): void
    {
        Log::shouldReceive('channel')->with('contact')->once()->andReturnSelf();

        // Expect the listener's structured log entry exactly once.
        Log::shouldReceive('info')
            ->once()
            ->with('Contact score processed', \Mockery::on(function (array $context) {
                return isset($context['id'], $context['email'], $context['score'], $context['status']);
            }));

        // Allow any other info() calls (e.g. the LogBroadcaster output for the broadcast event).
        Log::shouldReceive('info')->withAnyArgs()->andReturn(null);

        $contact = Contact::reconstitute(
            id: 42,
            name: new ContactName('João Silva'),
            email: new Email('joao@empresa.com.br'),
            phone: new Phone('11987654321'),
            score: new Score(60),
            status: ContactStatus::Active,
            processedAt: new \DateTimeImmutable(),
        );

        event(new ContactScoreProcessed($contact));
    }

    public function test_domain_event_is_dispatched_after_processing(): void
    {
        Event::fake([ContactScoreProcessed::class]);

        /** @var \Infrastructure\Persistence\Eloquent\Models\ContactModel $model */
        $model = \Infrastructure\Persistence\Eloquent\Models\ContactModel::create([
            'name' => 'João Silva',
            'email' => 'joao@empresa.com.br',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        $useCase = app(\Application\UseCases\Contact\ProcessContactScoreUseCase::class);
        $useCase->execute($model->id);

        Event::assertDispatched(ContactScoreProcessed::class, function ($event) use ($model) {
            return $event->contact->id() === $model->id
                && $event->contact->score()->value() === 60;
        });
    }
}
