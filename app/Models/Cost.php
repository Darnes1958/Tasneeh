<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cost extends Model
{
    protected $connection = 'other';
    public function kyde()
    {
        return $this->morphMany(Kyde::class, 'kydeable');
    }
    public function Acc(){
        return $this->belongsTo(Acc::class);
    }
    public function Kazena(){
        return $this->belongsTo(Kazena::class);
    }
    public function Costtype(){
        return $this->belongsTo(Costtype::class);
    }
   public function Buy()
   {
       return $this->belongsTo(Buy::class);
   }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
