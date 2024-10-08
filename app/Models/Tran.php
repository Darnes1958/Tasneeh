<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Tran extends Model
{
    protected $connection = 'other';

    public function Factory()
    {
        return $this->belongsTo(Factory::class);
    }

    public function Item()
    {
        return $this->belongsTo(Item::class);
    }

    protected $appends = ['sub_tot'];

    public function getSubTotAttribute()
    {
        return $this->price*$this->quant;
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
