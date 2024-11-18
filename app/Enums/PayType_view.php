<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum PayType_view: int implements HasLabel,HasColor
{
  case نقدا = 0;
  case مصرفي = 1;
  case _ = 3;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::نقدا => 'success',
      self::مصرفي => 'info',
      self::_ => 'primary',
    };
  }

}


