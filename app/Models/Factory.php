<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Factory extends Model
{
    protected $connection = 'other';

    protected $casts = [
        'status' => Status::class,
    ];
    protected $appends = ['price_tot','cost_one'];

    public function getPriceTotAttribute()
    {
        return $this->price*$this->quantity;
    }
    public function getCostOneAttribute()
    {
        return $this->cost / $this->quantity;
    }

    public function Product()
    {
        return $this->belongsTo(Product::class);
    }
    public function Place()
    {
        return $this->belongsTo(Place::class);
    }
    public function Tran(){
        return $this->hasMany(Tran::class);
    }
    public function Hand()
    {
        return $this->hasMany(Hand::class);
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
