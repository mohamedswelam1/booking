<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_create_service(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'name' => 'Haircut',
            'description' => 'Basic haircut',
            'duration' => 30,
            'price' => 25.00,
            'is_published' => true,
        ];

        $response = $this->actingAs($provider)
            ->postJson('/api/v1/provider/services', $payload)
            ->assertCreated()
            ->assertJsonStructure([
                'status',
                'message', 
                'data' => [
                    'id',
                    'name',
                    'description',
                    'duration',
                    'price',
                    'is_published',
                    'category_id',
                    'provider_id',
                ]
            ]);
            
        $this->assertEquals($provider->id, $response->json('data.provider_id'));
    }
}


