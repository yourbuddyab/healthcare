<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    public function test_appointment_list_successfully(): void
    {
        $payload = [
            'status' => 'booked',
            'order' => 'desc',
            'page' => '1',
            'count' => 10,
        ];

        $response = $this->getJson(route('appointment.index', $payload));

        $response->assertStatus(200)->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    public function test_appointment_list_with_invalid_parms(): void
    {
        $payload = [
            'status' => 'bookeds',
            'order' => 'top',
            'page' => 'selet',
            'count' => "ddd",
        ];

        $response = $this->getJson(route('appointment.index', $payload));

        $response->assertStatus(422)->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }
}
