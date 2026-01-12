<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AvailableSlotCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_days' => $this->collection->count(),
                'total_slots' => $this->collection->sum(fn($day) => count($day['slots'])),
            ],
        ];
    }
}
