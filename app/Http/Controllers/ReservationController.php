<?php

namespace App\Http\Controllers;

use App\Enums\RoleType;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationCollection;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService,
        protected AvailabilityService $availabilityService
    ) {
    }

    public function index(Request $request): ReservationCollection
    {
        $this->authorize('viewAny', \App\Models\Reservation::class);

        $user = auth()->user();
        $queryParams = $request->query();

        // Admins can see all reservations
        if ($user->role_id === RoleType::CLIENT->value) {
            $queryParams['user_id'] = $user->id;
        }

        $reservations = $this->reservationService->getReservations($queryParams);

        return new ReservationCollection($reservations);
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Reservation::class);

        $data = $request->validated();

        $user = auth()->user();
        if ($user->role_id === RoleType::CLIENT->value) {
            $data['user_id'] = $user->id;
        }

        $availability = $this->availabilityService->checkAvailability(
            $data['space_id'],
            $data['start'],
            $data['end']
        );

        if (!$availability->isAvailable) {
            return response()->json([
                'message' => $availability->message,
            ], $availability->statusCode);
        }

        $reservation = $this->reservationService->createReservation($data);

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): ReservationResource|JsonResponse
    {
        $reservation = $this->reservationService->getReservation($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reservation not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('view', $reservation);

        return new ReservationResource($reservation);
    }

    public function update(UpdateReservationRequest $request, int $id): ReservationResource|JsonResponse
    {
        $reservation = $this->reservationService->getReservation($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reservation not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('update', $reservation);

        $data = $request->validated();

        $availability = $this->availabilityService->checkAvailability(
            $data['space_id'] ?? $reservation->space_id,
            $data['start'] ?? $reservation->start,
            $data['end'] ?? $reservation->end,
            $reservation->id
        );

        if (!$availability->isAvailable) {
            return response()->json([
                'message' => $availability->message,
            ], $availability->statusCode);
        }

        $updatedReservation = $this->reservationService->updateReservation($reservation, $data);

        return new ReservationResource($updatedReservation);
    }

    public function destroy(int $id): JsonResponse
    {
        $reservation = $this->reservationService->getReservation($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reservation not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('delete', $reservation);

        $this->reservationService->deleteReservation($reservation);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
