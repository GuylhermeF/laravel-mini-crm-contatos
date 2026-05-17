<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Infrastructure\Persistence\Eloquent\Models\ContactModel;
use Infrastructure\Queue\Jobs\ProcessContactScoreJob;
use Tests\TestCase;

final class ContactScoreProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_score_endpoint_dispatches_job(): void
    {
        Queue::fake();

        $contact = ContactModel::create([
            'name' => 'João Silva',
            'email' => 'joao@empresa.com.br',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/contacts/{$contact->id}/process-score");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Score processing has been queued.']);

        Queue::assertPushed(ProcessContactScoreJob::class, function ($job) use ($contact) {
            return $job->contactId === $contact->id;
        });
    }

    public function test_process_score_returns_404_for_nonexistent_contact(): void
    {
        $response = $this->postJson('/api/contacts/99999/process-score');
        $response->assertStatus(404);
    }

    public function test_job_calculates_and_saves_score(): void
    {
        $contact = ContactModel::create([
            'name' => 'João Silva',
            'email' => 'joao@empresa.com.br',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        // Event::fake must be called BEFORE app() resolves the use case so that
        // LaravelEventDispatcher receives the faked dispatcher via constructor injection.
        \Illuminate\Support\Facades\Event::fake([
            \Domain\Contact\Events\ContactScoreProcessed::class,
        ]);

        $useCase = app(\Application\UseCases\Contact\ProcessContactScoreUseCase::class);

        $result = $useCase->execute($contact->id);

        $this->assertEquals('active', $result->status()->value);
        $this->assertEquals(60, $result->score()->value()); // corporate+.br+full name+SP = 60
        $this->assertNotNull($result->processedAt());

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'active',
            'score' => 60,
        ]);

        \Illuminate\Support\Facades\Event::assertDispatched(
            \Domain\Contact\Events\ContactScoreProcessed::class
        );
    }

    public function test_process_score_returns_422_for_already_processed_contact(): void
    {
        $contact = ContactModel::create([
            'name' => 'João Silva',
            'email' => 'joao@empresa.com.br',
            'phone' => '11987654321',
            'score' => 60,
            'status' => 'active',
        ]);

        $response = $this->postJson("/api/contacts/{$contact->id}/process-score");

        $response->assertStatus(422);
    }

    public function test_process_score_returns_422_for_contact_already_processing(): void
    {
        $contact = ContactModel::create([
            'name' => 'João Silva',
            'email' => 'joao2@empresa.com.br',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'processing',
        ]);

        $response = $this->postJson("/api/contacts/{$contact->id}/process-score");

        $response->assertStatus(422);
    }

    public function test_phone_normalization_via_model_observer(): void
    {
        $contact = ContactModel::create([
            'name' => 'Test User',
            'email' => 'test@empresa.com',
            'phone' => '(11) 98765-4321', // formatted input
            'score' => 0,
            'status' => 'pending',
        ]);

        $this->assertEquals('11987654321', $contact->fresh()->phone);
    }
}
