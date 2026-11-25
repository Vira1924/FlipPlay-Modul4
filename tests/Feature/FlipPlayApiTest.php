<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlipPlayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_initial_media_users_and_collection(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('media', 4);
        $this->assertDatabaseCount('users', 2);

        $primaryUser = User::where('email', 'pengguna@flipplay.test')->firstOrFail();
        $this->assertCount(2, $primaryUser->media);
    }

    public function test_can_list_media_with_pagination_and_without_unloaded_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->getJson('/api/media');

        $response->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'type', 'genre', 'description', 'episodes', 'format'],
                ],
                'links',
                'meta',
            ]);

        $this->assertArrayNotHasKey('users', $response->json('data.0'));
    }

    public function test_can_filter_media_by_title_and_type(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->getJson('/api/media?title=Jujutsu');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Jujutsu Kaisen');

        $typeResponse = $this->getJson('/api/media?type=Manga');

        $typeResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Blue Lock');
    }

    public function test_show_media_includes_users_relation_via_when_loaded(): void
    {
        $this->seed(DatabaseSeeder::class);
        $media = Media::where('title', 'Jujutsu Kaisen')->firstOrFail();

        $response = $this->getJson("/api/media/{$media->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $media->id)
            ->assertJsonPath('data.title', 'Jujutsu Kaisen')
            ->assertJsonCount(1, 'data.users')
            ->assertJsonPath('data.users.0.name', 'Pengguna FlipPlay');
    }

    public function test_show_non_existent_media_returns_404(): void
    {
        $response = $this->getJson('/api/media/9999');

        $response->assertNotFound();
    }

    public function test_can_create_media_and_returns_201(): void
    {
        $payload = [
            'title' => 'Attack on Titan',
            'type' => 'Anime',
            'genre' => 'Action, Dark Fantasy',
            'description' => 'Pertarungan umat manusia melawan Titan.',
            'episodes' => 89,
        ];

        $response = $this->postJson('/api/media', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Attack on Titan')
            ->assertJsonPath('data.episodes', 89);

        $this->assertDatabaseHas('media', ['title' => 'Attack on Titan']);
    }

    public function test_create_media_validation_fails_with_422_even_without_accept_json_header(): void
    {
        Media::create([
            'title' => 'Solo Leveling',
            'type' => 'Anime',
        ]);

        // Missing required fields without Accept: application/json
        $emptyResponse = $this->post('/api/media', []);
        $emptyResponse->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'type']);

        // Duplicate title
        $duplicateResponse = $this->postJson('/api/media', [
            'title' => 'Solo Leveling',
            'type' => 'Manga',
        ]);
        $duplicateResponse->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_can_partially_update_media_and_blocks_duplicate_title(): void
    {
        $media1 = Media::create(['title' => 'Naruto', 'type' => 'Anime', 'episodes' => 220]);
        $media2 = Media::create(['title' => 'One Piece', 'type' => 'Anime', 'episodes' => 1100]);

        // Partial update without changing title
        $okResponse = $this->patchJson("/api/media/{$media1->id}", [
            'episodes' => 500,
        ]);

        $okResponse->assertOk()
            ->assertJsonPath('data.title', 'Naruto')
            ->assertJsonPath('data.episodes', 500);

        // Updating title to another existing media's title must fail with 422
        $dupResponse = $this->patchJson("/api/media/{$media1->id}", [
            'title' => $media2->title,
        ]);

        $dupResponse->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_can_delete_media(): void
    {
        $media = Media::create(['title' => 'Bleach', 'type' => 'Anime']);

        $response = $this->deleteJson("/api/media/{$media->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Media berhasil dihapus']);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_user_collection_attach_duplicate_view_and_detach_flow(): void
    {
        $user = User::factory()->create(['name' => 'Elvira']);
        $media = Media::create(['title' => 'Frieren', 'type' => 'Anime', 'episodes' => 28]);

        // Attach media to user collection
        $attachResponse = $this->postJson("/api/users/{$user->id}/collection", [
            'media_id' => $media->id,
        ]);
        $attachResponse->assertCreated()
            ->assertJsonPath('message', 'Media berhasil ditambahkan ke koleksi')
            ->assertJsonCount(1, 'data.media');

        // Duplicate attach returns 409 Conflict
        $duplicateResponse = $this->postJson("/api/users/{$user->id}/collection", [
            'media_id' => $media->id,
        ]);
        $duplicateResponse->assertStatus(409)
            ->assertJson(['message' => 'Media sudah ada di koleksi']);

        // Invalid media_id returns 422
        $invalidResponse = $this->postJson("/api/users/{$user->id}/collection", [
            'media_id' => 99999,
        ]);
        $invalidResponse->assertStatus(422)
            ->assertJsonValidationErrors(['media_id']);

        // View user collection
        $collectionResponse = $this->getJson("/api/users/{$user->id}/collection");
        $collectionResponse->assertOk()
            ->assertJsonPath('user.name', 'Elvira')
            ->assertJsonCount(1, 'collection')
            ->assertJsonPath('collection.0.title', 'Frieren');

        // Detach media from user collection
        $detachResponse = $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}");
        $detachResponse->assertOk()
            ->assertJsonPath('message', 'Media berhasil dihapus dari koleksi')
            ->assertJsonCount(0, 'data.media');

        // Detaching again returns 404
        $missingDetachResponse = $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}");
        $missingDetachResponse->assertNotFound();
    }
}
