<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\AppointmentFilterRequest;
use App\Models\Appointment;

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
}
