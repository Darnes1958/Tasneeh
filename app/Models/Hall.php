<?php

namespace App\Models;

use App\Enums\PlaceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Hall extends Model
{
    protected $connection = 'other';


   public function account()
   {
       return $this->morphOne(Account::class, 'accountable');
   }
    public function Sell()
    {
        return $this->hasMany(Sell::class);
    }
    public function Salary()
    {
        return $this->hasMany(Salary::class);
    }
    public function Rent()
    {
        return $this->hasMany(Rent::class);
    }
    public function Masrofat()
    {
        return $this->hasMany(Masrofat::class);
    }

    public function Hall_Stock()
    {
        return $this->hasMany(Hall_Stock::class);
    }
    public function Hall_tran1()
    {
        return $this->hasMany(Hall_tran::class,'hall_id1');
    }
    public function Hall_tran2()
    {
        return $this->hasMany(Hall_tran::class,'hall_id2');
    }
    protected $casts=[
        'hall_type'=>PlaceType::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;
        }
    }
}
