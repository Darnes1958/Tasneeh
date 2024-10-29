<?php

namespace App\Models;

use App\Enums\Jomla;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Thiagoprz\CompositeKey\HasCompositeKey;


class Sell extends Model
{
  protected $connection = 'other';

    protected $appends = ['baky','total'];

    public function getBakyAttribute()
    {
        return $this->tot-$this->pay;
    }
    public function getTotalAttribute(){
        return $this->tot-$this->ksm;
    }
    public function kyde()
    {
        return $this->morphMany(Kyde::class, 'kydeable');
    }
  public function Customer(){
    return $this->belongsTo(Customer::class);
  }

  public function Hall(){
    return $this->belongsTo(Hall::class);
  }

  public function Sell_tran(){
    return $this->hasMany(Sell_tran::class);
  }
  public function Tar_sell(){
    return $this->hasMany(Tar_sell::class);
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
