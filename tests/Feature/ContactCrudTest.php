<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Contact\Enums\ContactStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Persistence\Eloquent\Models\ContactModel;
use Tests\TestCase;

final class ContactCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_contact(): void
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'João Silva',
            'email' => 'joao@empresa.com.br',
            'phone' => '11987654321',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'name', 'email', 'phone', 'score', 'status', 'status_label', 'processed_at',
            ])
            ->assertJsonFragment([
                'name' => 'João Silva',
                'email' => 'joao@empresa.com.br',
                'score' => 0,
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('contacts', [
            'email' => 'joao@empresa.com.br',
            'status' => 'pending',
            'score' => 0,
        ]);
    }

    public function test_cannot_create_contact_with_invalid_email(): void
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'João Silva',
            'email' => 'not-valid',
            'phone' => '11987654321',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_create_contact_with_missing_fields(): void
    {
        $response = $this->postJson('/api/contacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
    }

    public function test_cannot_create_duplicate_email(): void
    {
        ContactModel::create([
            'name' => 'Existing User',
            'email' => 'existing@empresa.com',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/contacts', [
            'name' => 'New User',
            'email' => 'existing@empresa.com',
            'phone' => '21987654321',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_list_contacts_with_pagination(): void
    {
        ContactModel::factory()->count(20)->create();

        $response = $this->getJson('/api/contacts?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'email', 'phone', 'score', 'status']],
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(20, $response->json('meta.total'));
    }

    public function test_can_show_a_contact(): void
    {
        $contact = ContactModel::create([
            'name' => 'Maria Santos',
            'email' => 'maria@empresa.com',
            'phone' => '19987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/contacts/{$contact->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $contact->id,
                'name' => 'Maria Santos',
                'email' => 'maria@empresa.com',
            ]);
    }

    public function test_returns_404_when_contact_not_found(): void
    {
        $response = $this->getJson('/api/contacts/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_a_contact(): void
    {
        $contact = ContactModel::create([
            'name' => 'Old Name',
            'email' => 'old@empresa.com',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/contacts/{$contact->id}", [
            'name' => 'New Name',
            'email' => 'new@empresa.com',
            'phone' => '19987654321',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'New Name',
                'email' => 'new@empresa.com',
            ]);

        $this->assertDatabaseHas('contacts', ['name' => 'New Name', 'email' => 'new@empresa.com']);
    }

    public function test_can_soft_delete_a_contact(): void
    {
        $contact = ContactModel::create([
            'name' => 'To Delete',
            'email' => 'delete@empresa.com',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    public function test_deleted_contact_returns_404(): void
    {
        $contact = ContactModel::create([
            'name' => 'To Delete',
            'email' => 'delete2@empresa.com',
            'phone' => '11987654321',
            'score' => 0,
            'status' => 'pending',
        ]);

        $contact->delete();

        $response = $this->getJson("/api/contacts/{$contact->id}");
        $response->assertStatus(404);
    }
}
