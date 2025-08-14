<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AppointmentCancelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Professional $professional;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
        $this->professional = Professional::factory()->create();
    }

    public function test_authorized_user_can_cancel_appointment_more_than_24_hours_before_start(): void
    {
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'professional_id' => $this->professional->id,
            'status' => 'booked',
            'start_time' => Carbon::now()->addDays(4),
            'end_time' => Carbon::now()->addDays(4)->addHour(),
        ]);

        $response = $this->getJson(route('appointment.cancel', $appointment->id));
        Log::debug($response->json());

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Appointment cancelled successfully',
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_cancel_appointment_within_24_hours(): void
    {
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'professional_id' => $this->professional->id,
            'status' => 'booked',
            'start_time' => Carbon::now()->addHours(10),
            'end_time' => Carbon::now()->addHours(11),
        ]);

        $response = $this->getJson(route('appointment.cancel', $appointment->id));

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Appointments cannot be cancelled within 24 hours of the start time.',
            ]);
    }

    public function test_cannot_cancel_completed_appointment(): void
    {
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'professional_id' => $this->professional->id,
            'status' => 'completed',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
        ]);

        $response = $this->getJson(route('appointment.cancel', $appointment->id));

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => "That appointment is already completed. You cannot cancel it.",
            ]);
    }

    public function test_unauthorized_user_cannot_cancel_appointment(): void
    {
        $otherUser = User::factory()->create();

        $appointment = Appointment::create([
            'user_id' => $otherUser->id,
            'professional_id' => $this->professional->id,
            'status' => 'booked',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
        ]);

        $response = $this->getJson(route('appointment.cancel', $appointment->id));

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'You are not authorized to cancel this appointment.'
            ]);
    }
}
