<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AppointmentCompleteTest extends TestCase
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

    public function test_unauthorized_user_cannot_complated_appointment(): void
    {
        $otherUser = User::factory()->create();

        $appointment = Appointment::create([
            'user_id' => $otherUser->id,
            'professional_id' => $this->professional->id,
            'status' => 'booked',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
        ]);

        $response = $this->getJson(route('appointment.complete', $appointment->id));

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'You are not authorized to cancel this appointment.'
            ]);
    }

    public function test_cannot_complete_before_appointment_time(): void
    {
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'professional_id' => $this->professional->id,
            'status' => 'booked',
            'start_time' => Carbon::now()->addHours(10),
            'end_time' => Carbon::now()->addHours(11),
        ]);

        $response = $this->getJson(route('appointment.complete', $appointment->id));

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Appointments cannot be completed before appointment time.',
            ]);
    }

    public function test_complete_after_appointment_time(): void
    {
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'professional_id' => $this->professional->id,
            'status' => 'booked',
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->subHours(1),
        ]);

        $response = $this->getJson(route('appointment.complete', $appointment->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
        ]);
    }
}
