<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Hall_tran extends Model
{
    protected $connection = 'other';

    public function Hall1(){
        return $this->belongsTo(Hall::class,'hall_id1');
    }
    public function Hall2(){
        return $this->belongsTo(Hall::class,'hall_id2');
    }

    public function Product(){
        return $this->belongsTo(Product::class);
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;
        }
    }
}
