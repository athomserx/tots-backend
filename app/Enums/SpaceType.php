<?php

namespace App\Enums;

enum SpaceType: string
{
  case AUDITORIUM = 'auditorio';
  case CONFERENCE_ROOM = 'sala_de_reuniones';
  case OFFICE = 'oficina';
  case LABORATORY = 'laboratorio';

  public function label(): string
  {
    return match ($this) {
      self::AUDITORIUM => 'Auditorio',
      self::CONFERENCE_ROOM => 'Sala de Reuniones',
      self::OFFICE => 'Oficina',
      self::LABORATORY => 'Laboratorio',
    };
  }
}