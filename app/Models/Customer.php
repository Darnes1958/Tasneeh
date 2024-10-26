<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
  protected $connection = 'other';

    public function account()
    {
        return $this->morphOne(Account::class, 'accountable');
    }
  public function Sell(){
    return $this->hasMany(Sell::class);
  }

    public function Receipt(){
        return $this->hasMany(Receipt::class);
    }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
