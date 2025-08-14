<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentService
{
    public function isProfessionalAvailable($professional_id, $start_time, $end_time)
    {
        return !Appointment::where('professional_id', $professional_id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time])
                    ->orWhere(function ($q) use ($start_time, $end_time) {
                        $q->where('start_time', '<=', $start_time)
                            ->where('end_time', '>=', $end_time);
                    });
            })
            ->exists();
    }

    public function hasUserPendingAppointment($user_id, $professional_id)
    {
        return Appointment::where(['user_id' => $user_id, 'professional_id' => $professional_id])
            ->where('status', 'booked')
            ->where('start_time', '>=', Carbon::now())
            ->exists();
    }

    public function isTimeSlotAvailable($user_id, $start_time, $end_time)
    {
        return !Appointment::where('user_id', $user_id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time])
                    ->orWhere(function ($q) use ($start_time, $end_time) {
                        $q->where('start_time', '<=', $start_time)
                            ->where('end_time', '>=', $end_time);
                    });
            })
            ->exists();
    }


    public function createAppointment($data)
    {
        return Appointment::create($data);
    }
}
