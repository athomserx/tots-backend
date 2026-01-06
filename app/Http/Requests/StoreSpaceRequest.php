<?php

namespace App\Http\Requests;

use App\Enums\SpaceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Add authorization logic for only admins can create spaces
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', Rule::in(array_map(fn($case) => $case->value, SpaceType::cases()))],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The space name is required.',
            'description.required' => 'The space description is required.',
            'price_per_hour.required' => 'The price per hour is required.',
            'price_per_hour.numeric' => 'The price per hour must be a number.',
            'price_per_hour.min' => 'The price per hour must be at least 0.',
            'capacity.required' => 'The capacity is required.',
            'capacity.integer' => 'The capacity must be an integer.',
            'capacity.min' => 'The capacity must be at least 1.',
            'type.required' => 'The space type is required.',
            'type.in' => 'The selected space type is invalid.',
            'images.*.url' => 'Each image must be a valid URL.',
        ];
    }
}
