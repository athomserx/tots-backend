<?php

namespace Tests\Feature;

use App\Models\AvailabilityRule;
use App\Models\Exception as ExceptionModel;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use App\Enums\RoleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class ReservationAvailabilityTest extends TestCase
{
  use RefreshDatabase;

  protected $user;
  protected $space;

  protected function setUp(): void
  {
    parent::setUp();

    \App\Models\Role::create(['id' => RoleType::CLIENT->value, 'name' => 'client']);

    $this->user = User::factory()->create(['role_id' => RoleType::CLIENT->value]);
    $this->space = Space::create([
      'name' => 'Test Space',
      'description' => 'Test Description',
      'type' => 'event',
      'price_per_hour' => 100,
      'capacity' => 10,
      'images' => [],
    ]);
  }

  public function test_reservation_blocked_by_exception_closed()
  {
    $date = Carbon::tomorrow();
    ExceptionModel::create([
      'space_id' => $this->space->id,
      'date' => $date->toDateString(),
      'is_closed' => true,
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/reservations', [
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(10)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->toDateTimeString(),
      'type' => 'event',
      'event_name' => 'Test',
    ]);

    $response->assertStatus(422)
      ->assertJson(['message' => 'The space is closed on this date due to an exception.']);
  }

  public function test_reservation_allowed_by_exception_override_time()
  {
    $date = Carbon::tomorrow();
    ExceptionModel::create([
      'space_id' => $this->space->id,
      'date' => $date->toDateString(),
      'is_closed' => false,
      'override_open_time' => '09:00:00',
      'override_close_time' => '13:00:00',
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/reservations', [
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(10)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->toDateTimeString(),
      'type' => 'event',
      'event_name' => 'Test',
    ]);

    $response->assertStatus(201);
  }

  public function test_reservation_blocked_by_exception_override_time()
  {
    $date = Carbon::tomorrow();
    ExceptionModel::create([
      'space_id' => $this->space->id,
      'date' => $date->toDateString(),
      'is_closed' => false,
      'override_open_time' => '09:00:00',
      'override_close_time' => '11:00:00',
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/reservations', [
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(10)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->toDateTimeString(), // Ends after 11
      'type' => 'event',
      'event_name' => 'Test',
    ]);

    $response->assertStatus(422)
      ->assertJson(['message' => 'The reservation time is outside the special operating hours for this date.']);
  }

  public function test_reservation_blocked_no_rule_found()
  {
    $date = Carbon::tomorrow();

    $response = $this->actingAs($this->user)->postJson('/api/reservations', [
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(10)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->toDateTimeString(),
      'type' => 'event',
      'event_name' => 'Test',
    ]);

    $response->assertStatus(422)
      ->assertJson(['message' => 'No availability rules defined for this day.']);
  }

  public function test_reservation_allowed_by_rule()
  {
    $date = Carbon::tomorrow();
    AvailabilityRule::create([
      'space_id' => $this->space->id,
      'day_of_week' => $date->dayOfWeek,
      'open_time' => '08:00:00',
      'close_time' => '18:00:00',
      'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/reservations', [
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(10)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->toDateTimeString(),
      'type' => 'event',
      'event_name' => 'Test',
    ]);

    $response->assertStatus(201);
  }

  public function test_reservation_blocked_by_rule_time()
  {
    $date = Carbon::tomorrow();
    AvailabilityRule::create([
      'space_id' => $this->space->id,
      'day_of_week' => $date->dayOfWeek,
      'open_time' => '08:00:00',
      'close_time' => '10:00:00',
      'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/reservations', [
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(9)->toDateTimeString(),
      'end' => $date->copy()->setHour(11)->toDateTimeString(), // Ends after 10
      'type' => 'event',
      'event_name' => 'Test',
    ]);

    $response->assertStatus(422)
      ->assertJson(['message' => 'The reservation time is outside the operating hours.']);
  }

  public function test_reservation_blocked_by_overlap()
  {
    $date = Carbon::tomorrow();
    AvailabilityRule::create([
      'space_id' => $this->space->id,
      'day_of_week' => $date->dayOfWeek,
      'open_time' => '08:00:00',
      'close_time' => '18:00:00',
      'is_active' => true,
    ]);

    Reservation::create([
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(10)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->toDateTimeString(),
      'user_id' => $this->user->id,
      'type' => 'event',
      'event_name' => 'Existing',
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/reservations', [
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(11)->toDateTimeString(),
      'end' => $date->copy()->setHour(13)->toDateTimeString(),
      'type' => 'event',
      'event_name' => 'Test',
    ]);

    $response->assertStatus(409)
      ->assertJson(['message' => 'The space is already booked for the selected time.']);
  }

  public function test_update_reservation_allowed_same_overlap()
  {
    $date = Carbon::tomorrow();
    AvailabilityRule::create([
      'space_id' => $this->space->id,
      'day_of_week' => $date->dayOfWeek,
      'open_time' => '08:00:00',
      'close_time' => '18:00:00',
      'is_active' => true,
    ]);

    $reservation = Reservation::create([
      'space_id' => $this->space->id,
      'start' => $date->copy()->setHour(10)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->toDateTimeString(),
      'user_id' => $this->user->id,
      'type' => 'event',
      'event_name' => 'Existing',
    ]);

    $response = $this->actingAs($this->user)->putJson("/api/reservations/{$reservation->id}", [
      'start' => $date->copy()->setHour(10)->setMinute(30)->toDateTimeString(),
      'end' => $date->copy()->setHour(12)->setMinute(30)->toDateTimeString(),
      'type' => 'event',
      'event_name' => 'Test',
    ]);
    $response->assertStatus(200);
  }
}
