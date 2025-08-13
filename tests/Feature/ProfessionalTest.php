<?php

namespace Tests\Feature;

use App\Models\Professional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_professional_list_successully()
    {
        Professional::factory()->count(20)->create();
        $payload = [
            'order' => 'asc',
            'page'  => 2,
            'count' => 10
        ];
        $response = $this->getJson(route('professional.index', $payload));

        $response->assertStatus(200)->assertJsonStructure([
            'status',
            'message',
            'data' => [
                [
                    'id',
                    'name',
                    'specialty'
                ]
            ]
        ]);
    }

    public function test_professional_list_fails_with_invalid_data()
    {
        Professional::factory()->count(20)->create();
        $payload = [
            'page' => "number",
            'order' => "top",
            'count' => "all"
        ];

        $response = $this->getJson(route('professional.index', $payload));

        $response->assertStatus(422)->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }
}
