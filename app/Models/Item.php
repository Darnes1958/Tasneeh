<?php

namespace App\Models;

use App\Enums\TwoUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    protected $connection = 'other';



    public function placeStocks(){
      return $this->hasMany(Place_stock::class);
    }
  public function Price_buy(){
    return $this->hasMany(Price_buy::class);
  }
    public function Price_sell(){
        return $this->hasMany(Price_sell::class);
    }

    public function Item_type(){
        return $this->belongsTo(Item_type::class);
    }
    public function Place(){
        return $this->belongsTo(Place::class);
    }
    public function Unit(){
        return $this->belongsTo(Unit::class);
    }



  public function Buy_tran(){
    return $this->hasMany(Buy_tran::class);
  }

  public function Tar_Buy(){
  return $this->hasMany(Tar_buy::class);
}

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }

}
