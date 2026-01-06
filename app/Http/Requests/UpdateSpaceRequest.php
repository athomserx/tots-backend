<?php

namespace App\Http\Requests;

use App\Enums\SpaceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Add authorization logic for only admins can update spaces
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'description' => ['string'],
            'price_per_hour' => ['numeric', 'min:0'],
            'capacity' => ['integer', 'min:1'],
            'type' => ['string', Rule::in(array_map(fn($case) => $case->value, SpaceType::cases()))],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'url'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'The space name must be a string.',
            'description.string' => 'The space description must be a string.',
            'price_per_hour.numeric' => 'The price per hour must be a number.',
            'price_per_hour.min' => 'The price per hour must be at least 0.',
            'capacity.integer' => 'The capacity must be an integer.',
            'capacity.min' => 'The capacity must be at least 1.',
            'type.in' => 'The selected space type is invalid.',
            'images.*.url' => 'Each image must be a valid URL.',
        ];
    }
}
