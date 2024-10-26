<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum AccLevel: int implements HasLabel,HasColor
{
  case رئيسي = 1;
  case فرعي = 2;
  case تحليلي = 3;
  case تحليلي_مساعد = 4;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::رئيسي => 'success',
      self::فرعي => 'info',
      self::تحليلي => 'primary',
      self::تحليلي_مساعد => 'Fuchsia',
    };
  }

}


