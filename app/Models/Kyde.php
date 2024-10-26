<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Kyde extends Model
{
    protected $connection = 'other';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company.'Acc';

        }
    }
    protected $appends=['tot_mden','tot_daen'];
    public function getTotMdenAttribute()
    {
        return $this->KydeData->sum('mden');
    }
    public function getTotDaenAttribute()
    {
        return $this->KydeData->sum('daen');
    }

    public function kydeable()
    {
        return $this->morphTo();
    }
    public function KydeData()
    {
       return $this->hasMany(KydeData::class);
    }
}
