<?php

namespace App\Http\Requests\Appointment;

use App\Http\Requests\BaseApiRequest;

class AppointmentFilterRequest extends BaseApiRequest
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
            'status' => 'in:booked,completed,cancelled',
            'order' => 'in:desc,asc',
            'page'   => 'numeric',
            'count'   => 'numeric'
        ];
    }
}
