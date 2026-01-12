<?php

namespace App\Services;

use App\DTOs\AvailabilityResult;
use App\Models\AvailabilityRule;
use App\Models\Exception as ExceptionModel;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Response;

class AvailabilityService
{
  public function checkAvailability(int $spaceId, string $start, string $end, ?int $excludeReservationId = null): AvailabilityResult
  {
    $startDate = Carbon::parse($start);
    $endDate = Carbon::parse($end);
    $dayOfWeek = $startDate->dayOfWeek;
    $dateString = $startDate->toDateString();
    $startTime = $startDate->toTimeString();
    $endTime = $endDate->toTimeString();

    $exception = ExceptionModel::where('date', $dateString)
      ->where(function ($query) use ($spaceId) {
        $query->where('space_id', $spaceId)
          ->orWhereNull('space_id');
      })
      // Prioritize specific space exception if both exist
      ->orderByRaw('CASE WHEN space_id IS NOT NULL THEN 1 ELSE 2 END')
      ->first();

    if ($exception) {
      if ($exception->is_closed) {
        return new AvailabilityResult(false, 'The space is closed on this date due to an exception.', Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      // If not closed, check for override times
      if ($exception->override_open_time && $exception->override_close_time) {
        if ($startTime < $exception->override_open_time || $endTime > $exception->override_close_time) {
          return new AvailabilityResult(false, 'The reservation time is outside the special operating hours for this date.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
      }
      // If exception exists and is not closed, we ignore availability rules as per requirement.
    } else {
      $rule = AvailabilityRule::where('day_of_week', $dayOfWeek)
        ->where('is_active', true)
        ->where(function ($query) use ($spaceId) {
          $query->where('space_id', $spaceId)
            ->orWhereNull('space_id');
        })
        ->orderByRaw('CASE WHEN space_id IS NOT NULL THEN 1 ELSE 2 END')
        ->first();

      if (!$rule) {
        return new AvailabilityResult(false, 'No availability rules defined for this day.', Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      if ($startTime < $rule->open_time || $endTime > $rule->close_time) {
        return new AvailabilityResult(false, 'The reservation time is outside the operating hours.', Response::HTTP_UNPROCESSABLE_ENTITY);
      }
    }

    $overlapQuery = Reservation::where('space_id', $spaceId)
      ->where(function ($query) use ($start, $end) {
        $query->where(function ($q) use ($start, $end) {
          $q->where('start', '<', $end)
            ->where('end', '>', $start);
        });
      });

    if ($excludeReservationId) {
      $overlapQuery->where('id', '!=', $excludeReservationId);
    }

    if ($overlapQuery->exists()) {
      return new AvailabilityResult(false, 'The space is already booked for the selected time.', Response::HTTP_CONFLICT);
    }

    return new AvailabilityResult(true);
  }

  /**
   * Get all available time slots for a space from current date until one month ahead
   *
   * @param int $spaceId
   * @param int $slotDurationMinutes Duration of each time slot in minutes (default: 60)
   * @return array Array of available time slots grouped by date
   */
  public function getAvailableSlots(int $spaceId, int $slotDurationMinutes = 60): array
  {
    $startDate = Carbon::now()->startOfDay();
    $endDate = Carbon::now()->addMonth()->endOfDay();
    $availableSlots = [];

    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
      $dateString = $date->toDateString();
      $dayOfWeek = $date->dayOfWeek;
      $daySlots = [];

      $operatingHours = $this->getOperatingHours($spaceId, $dateString, $dayOfWeek);

      if (!$operatingHours) {
        continue;
      }

      if ($operatingHours['is_closed']) {
        continue;
      }
      $currentSlot = Carbon::parse($dateString . ' ' . $operatingHours['open_time']);
      $closeTime = Carbon::parse($dateString . ' ' . $operatingHours['close_time']);

      if ($closeTime->lt($currentSlot)) {
        $closeTime->addDay();
      }

      while ($currentSlot->lt($closeTime)) {
        $slotEnd = $currentSlot->copy()->addMinutes($slotDurationMinutes);

        if ($slotEnd->gt($closeTime)) {
          break;
        }
        $availability = $this->checkAvailability(
          $spaceId,
          $currentSlot->toIso8601String(),
          $slotEnd->toIso8601String()
        );

        if ($availability->isAvailable) {
          $daySlots[] = [
            'start' => $currentSlot->toIso8601String(),
            'end' => $slotEnd->toIso8601String(),
            'start_time' => $currentSlot->format('H:i'),
            'end_time' => $slotEnd->format('H:i'),
          ];
        }

        $currentSlot->addMinutes($slotDurationMinutes);
      }

      if (!empty($daySlots)) {
        $availableSlots[] = [
          'date' => $dateString,
          'day_of_week' => $dayOfWeek,
          'slots' => $daySlots,
        ];
      }
    }

    return $availableSlots;
  }

  /**
   * Get operating hours for a specific date
   *
   * @param int $spaceId
   * @param string $dateString
   * @param int $dayOfWeek
   * @return array|null Returns array with 'open_time', 'close_time', 'is_closed' or null if no rules
   */
  private function getOperatingHours(int $spaceId, string $dateString, int $dayOfWeek): ?array
  {
    $exception = ExceptionModel::where('date', $dateString)
      ->where(function ($query) use ($spaceId) {
        $query->where('space_id', $spaceId)
          ->orWhereNull('space_id');
      })
      ->orderByRaw('CASE WHEN space_id IS NOT NULL THEN 1 ELSE 2 END')
      ->first();

    if ($exception) {
      if ($exception->is_closed) {
        return ['is_closed' => true];
      }

      if ($exception->override_open_time && $exception->override_close_time) {
        return [
          'open_time' => $exception->override_open_time,
          'close_time' => $exception->override_close_time,
          'is_closed' => false,
        ];
      }
    }

    $rule = AvailabilityRule::where('day_of_week', $dayOfWeek)
      ->where('is_active', true)
      ->where(function ($query) use ($spaceId) {
        $query->where('space_id', $spaceId)
          ->orWhereNull('space_id');
      })
      ->orderByRaw('CASE WHEN space_id IS NOT NULL THEN 1 ELSE 2 END')
      ->first();

    if (!$rule) {
      return null;
    }

    return [
      'open_time' => $rule->open_time,
      'close_time' => $rule->close_time,
      'is_closed' => false,
    ];
  }
}
