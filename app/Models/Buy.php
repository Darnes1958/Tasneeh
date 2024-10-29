<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Buy extends Model
{
  protected $connection = 'other';



    protected $appends = ['baky','total'];

    public function getBakyAttribute()
    {
        return $this->tot-$this->ksm-$this->pay;
    }
   public function getTotalAttribute(){
        return $this->tot-$this->ksm;
   }
    public function kyde()
    {
        return $this->morphMany(Kyde::class, 'kydeable');
    }
  public function Supplier(){
    return $this->belongsTo(Supplier::class);
  }


  public function Place(){
    return $this->belongsTo(Place::class);
  }

  public function Buy_tran(){
    return $this->hasMany(Buy_tran::class);
  }
  public function Cost(){
      return $this->hasMany(Cost::class);
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
