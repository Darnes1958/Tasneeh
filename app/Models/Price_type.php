<?php

namespace App\Models;

use App\Enums\IncDec;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Price_type extends Model
{
  protected $connection = 'other';
  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts =[
    'inc_dec' => IncDec::class,
  ];



    public function Receipt(){
        return $this->hasMany(Receipt::class);
    }
    public function Recsupp(){
        return $this->hasMany(Recsupp::class);
    }

    public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
