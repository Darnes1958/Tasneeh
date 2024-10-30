<?php

namespace App\Models;

use App\Enums\PayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Renttran extends Model
{
    protected $connection = 'other';

    protected $casts =[

        'pay_type' => PayType::class,
    ];
    public function kyde()
    {
        return $this->morphMany(Kyde::class, 'kydeable');
    }
    public function Rent(){
        return $this->belongsTo(Rent::class);
    }
    public function Acc(){
        return $this->belongsTo(Acc::class);
    }

    public function Kazena(){
        return $this->belongsTo(Kazena::class);
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }

}
