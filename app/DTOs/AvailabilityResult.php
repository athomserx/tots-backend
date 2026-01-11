<?php

namespace App\DTOs;

class AvailabilityResult
{
  public function __construct(
    public bool $isAvailable,
    public ?string $message = null,
    public ?int $statusCode = 200
  ) {
  }
}
