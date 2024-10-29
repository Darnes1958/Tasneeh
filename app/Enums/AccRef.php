<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum AccRef: string implements HasLabel
{
  case halls = '2-5';
  case places = '2-4';
  case factories = '2-6';
  case suppliers = '4-1';
  case costs = '6-4';
  case mans = '6-3';
  case buys = '6-1';
  case makzoone = '6-2';

  case kazena = '2-1';

  case msarf = '2-2';



  public function getLabel(): ?string
  {
    return $this->name;
  }

}


