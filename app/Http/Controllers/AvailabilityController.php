<?php

namespace App\Http\Controllers;

use App\Http\Resources\AvailableSlotCollection;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AvailabilityController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {
    }

    /**
     * Get available time slots for a space from current date until one month ahead
     *
     * @param Request $request
     * @param int $spaceId
     * @return AvailableSlotCollection|JsonResponse
     */
    public function getAvailableSlots(Request $request, int $spaceId): AvailableSlotCollection|JsonResponse
    {
        $slotDuration = $request->query('slot_duration', 60);

        // Validate slot duration
        if (!is_numeric($slotDuration) || $slotDuration < 15 || $slotDuration > 480) {
            return response()->json([
                'message' => 'Slot duration must be between 15 and 480 minutes.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $availableSlots = $this->availabilityService->getAvailableSlots(
            $spaceId,
            (int) $slotDuration
        );

        return new AvailableSlotCollection($availableSlots);
    }
}
