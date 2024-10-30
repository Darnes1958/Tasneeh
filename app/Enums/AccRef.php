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
  case customers = '2-3-1';


  case buys = '6-1';
  case makzoone = '6-2';
  case mans = '6-3';
  case costs = '6-4';
  case masrofats = '6-5';
  case rents = '6-6';
  case rents_mden = '6-9';
  case salaries = '6-7';
  case salaries_mden = '6-8';
  case sells = '5-1';


  case kazena = '2-1';

  case msarf = '2-2';



  public function getLabel(): ?string
  {
    return $this->name;
  }

}


