<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Process: string implements HasLabel,HasColor,HasIcon
{
  case تحت_التصنيع = 'manufacturing';
  case جاهز = 'ready';


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::تحت_التصنيع => 'info',
      self::جاهز => 'success',
    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::تحت_التصنيع => 'heroicon-m-wrench-screwdriver',
      self::جاهز => 'heroicon-m-hand-thumb-up',

    };
  }
}


