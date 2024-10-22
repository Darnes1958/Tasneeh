<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum PayWho: int implements HasLabel,HasColor
{
  case اعمال = 0;
  case دفع_عن_عمل = 1;
  case دفع = 2;
  case خصم = 3;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::اعمال => 'info',
      self::قبض => 'primary',
      self::قبض_عن_عمل => 'success',
      self::خصم => 'danger',
    };
  }

}


