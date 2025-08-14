<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\AppointmentFilterRequest;
use App\Http\Requests\Appointment\AppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(AppointmentFilterRequest $request)
    {
        $appointments = Appointment::when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })->when($request->filled('order'), function ($query) use ($request) {
            $query->orderBy('start_time', $request->order);
        })->limit(empty($request->count) ? 10 : $request->count)
            ->with('professional')
            ->offset(empty($request->page) ? 0 : $request->page * 5)
            ->get();

        return response([
            'status' => true,
            'message' => "List of Appointments",
            'data' => $appointments
        ]);
    }

    public function store(AppointmentRequest $request, AppointmentService $appointmentService)
    {
        try {
            $user_id = Auth::id();

            if (!$appointmentService->isProfessionalAvailable(
                $request->professional_id,
                $request->start_time,
                $request->end_time
            )) {
                return response(['status' => false, 'message' => 'Professional is not available at this time.'], 409);
            }

            if ($appointmentService->isTimeSlotAvailable($user_id, $request->start_time, $request->end_time)) {
                return response(['status' => false, 'message' => 'You already have an appointment with this time.'], 409);
            }

            if ($appointmentService->hasUserPendingAppointment($user_id, $request->professional_id)) {
                return response(['status' => false, 'message' => 'You already have an appointment scheduled.'], 409);
            }

            $appointment = $appointmentService->createAppointment(array_merge($request->validated(), ['user_id' => $user_id]));

            return response(['status' => true, 'message' => 'Appointment successfully created.', 'data' => $appointment], 201);
        } catch (\Throwable $th) {
            return response(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function cancel(Appointment $appointment, AppointmentService $appointmentService)
    {
        if ($appointmentService->validatedUserId($appointment)) {
            return response([
                'status' => false,
                'message' => 'You are not authorized to cancel this appointment.'
            ], 403);
        }

        if ($appointment->status === 'completed') {
            return response([
                'status' => false,
                'message' => 'That appointment is already completed. You cannot cancel it.'
            ], 403);
        }

        $now = Carbon::now();
        $startTime = Carbon::parse($appointment->start_time);
        if ($now->diffInHours($startTime, false) < 24) {
            return response([
                'status' => false,
                'message' => 'Appointments cannot be cancelled within 24 hours of the start time.'
            ], 422);
        }

        $appointment->update(['status' => 'cancelled']);

        return response([
            'status' => true,
            'message' => 'Appointment cancelled successfully',
            'data' => $appointment
        ]);
    }
}
