<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Supplier extends Model
{
  protected $connection = 'other';
    public function account()
    {
        return $this->morphOne(Account::class, 'accountable');
    }
    public function kyde()
    {
        return $this->morphMany(Kyde::class, 'kydeable');
    }
  public function Buy(){
    return $this->hasMany(Buy::class);
  }
    public function Buys_work(){
        return $this->hasMany(Buys_work::class);
    }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
