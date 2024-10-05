<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Place_stock extends Model
{
  protected $connection = 'other';

  public function Place(){
    return $this->belongsTo(Place::class);
  }
  public function Item(){
    return $this->belongsTo(Item::class);
  }
    protected $with = ['Item'];
    protected $appends = ['sub_input','sub_cost'];

    public function getSubInputAttribute()
    {
        return $this->stock*$this->Item->price_buy;
    }
    public function getSubCostAttribute()
    {
        return $this->stock*$this->Item->price_cost;
    }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
