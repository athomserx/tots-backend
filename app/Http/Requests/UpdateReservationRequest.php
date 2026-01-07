<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'space_id' => ['sometimes', 'integer', 'exists:spaces,id'],
            'start' => ['sometimes', 'date'],
            'end' => ['sometimes', 'date', 'after:start'],
            'type' => ['sometimes', 'string', 'in:booking,block'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'space_id.exists' => 'The selected space does not exist.',
            'start.date' => 'The start date must be a valid date.',
            'end.date' => 'The end date must be a valid date.',
            'end.after' => 'The end date must be after the start date.',
            'type.in' => 'The reservation type must be either booking or block.',
        ];
    }
}
