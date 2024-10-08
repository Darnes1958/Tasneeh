<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Hall_stock extends Model
{
    protected $connection = 'other';

    public function Hall(){
        return $this->belongsTo(Hall::class);
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
