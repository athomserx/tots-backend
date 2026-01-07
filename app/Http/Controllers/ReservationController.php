<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationCollection;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService
    ) {
    }

    public function index(Request $request): ReservationCollection
    {
        $queryParams = $request->query();
        $reservations = $this->reservationService->getReservations($queryParams);

        return new ReservationCollection($reservations);
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->reservationService->createReservation($request->validated());

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

        $updatedReservation = $this->reservationService->updateReservation($reservation, $request->validated());

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

        $this->reservationService->deleteReservation($reservation);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
