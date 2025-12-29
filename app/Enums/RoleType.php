<?php

namespace App\Enums;

enum RoleType: int
{
  case CLIENT = 1;
  case ADMIN = 2;

  public function label(): string
  {
    return match ($this) {
      self::CLIENT => 'client',
      self::ADMIN => 'admin',
    };
  }
}