<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;
    protected $user, $professional;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
        $this->professional = Professional::factory()->create();
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

    public function test_appointment_with_invalid_data(): void
    {
        $payload = [
            'professional_id' => '',
            'start_time' => Carbon::now()->subDays(),
            'end_time' => Carbon::now()->subDays()->subHour(),
        ];
        $response = $this->postJson(route('appointment.store'), $payload);

        $response->assertStatus(422)->assertJsonStructure([
            'status',
            'message',
        ]);
    }

    public function test_professional_availability_check_for_appointment(): void
    {
        $payload = [
            'professional_id' => $this->professional->id,
            'start_time' => Carbon::now()->addHour()->format("Y-m-d H:i:s"),
            'end_time' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        Appointment::create(array_merge(
            $payload,
            ['user_id' => $this->user->id]
        ));
        $response = $this->postJson(route('appointment.store'), $payload);

        $response->assertStatus(409)->assertJsonStructure([
            'status',
            'message',
        ]);
    }

    public function  test_appointment_created_successfully(): void
    {
        $payload = [
            'professional_id' => $this->professional->id,
            'start_time' => Carbon::now()->addHours(4)->format("Y-m-d H:i:s"),
            'end_time' => Carbon::now()->addHours(5)->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson(route('appointment.store'), $payload);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'message'])
            ->assertJson([
                'status' => true,
                'message' => 'Appointment successfully created.',
            ]);
    }

    public function test_time_slot_can_be_booked_for_different_professional(): void
    {
        $appointmentPayload = [
            'professional_id' => $this->professional->id,
            'start_time' => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
        ];
        Appointment::create(array_merge($appointmentPayload, ['user_id' => $this->user->id]));

        $newProfessional = Professional::factory()->create();
        $payload = [
            'professional_id' => $newProfessional->id,
            'start_time' => $appointmentPayload['start_time'],
            'end_time' => $appointmentPayload['end_time'],
        ];

        $response = $this->postJson(route('appointment.store'), $payload);
        Log::debug($response->json());
        $response->assertStatus(409)
            ->assertJsonStructure(['status', 'message'])
            ->assertJson([
                'status' => false,
                'message' => 'You already have an appointment with this time.',
            ]);
    }


    public function test_appointment_already_exist_in_future_date(): void
    {
        Appointment::create([
            'user_id' => $this->user->id,
            'professional_id' => $this->professional->id,
            'start_time' => Carbon::now()->addDay()->format("Y-m-d H:i:s"),
            'end_time' => Carbon::now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $payload = [
            'professional_id' => $this->professional->id,
            'start_time' => Carbon::now()->addHour()->format("Y-m-d H:i:s"),
            'end_time' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
        ];
        $response = $this->postJson(route('appointment.store'), $payload);

        $response->assertStatus(409)->assertJsonStructure([
            'status',
            'message',
        ]);
    }
}
