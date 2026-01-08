<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'space_id' => ['required', 'integer', 'exists:spaces,id'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'type' => ['required', 'string', 'in:booking,block'],
            'event_name' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'space_id.required' => 'The space is required.',
            'space_id.exists' => 'The selected space does not exist.',
            'start.required' => 'The start date is required.',
            'start.date' => 'The start date must be a valid date.',
            'end.required' => 'The end date is required.',
            'end.date' => 'The end date must be a valid date.',
            'end.after' => 'The end date must be after the start date.',
            'type.required' => 'The reservation type is required.',
            'type.in' => 'The reservation type must be either booking or block.',
        ];
    }
}
