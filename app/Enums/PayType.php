<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum PayType: int implements HasLabel,HasColor
{
  case نقدا = 0;
  case مصرفي = 1;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::نقدا => 'success',
      self::مصرفي => 'info',
    };
  }

}


