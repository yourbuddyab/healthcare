<?php

namespace App\Http\Requests\Appointment;

use App\Http\Requests\BaseApiRequest;

class AppointmentRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'professional_id' => 'required|exists:professionals,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
            'end_time'   => 'required|date_format:Y-m-d H:i:s|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'start_time.after_or_equal' => 'Start time must be current or future time.',
        ];
    }
}
