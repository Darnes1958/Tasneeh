<?php

namespace App\Models;

use App\Enums\PayWho;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Hand extends Model
{
    protected $connection = 'other';

    public function Factory()
    {
        return $this->belongsTo(Factory::class);
    }
    public function Man()
    {
        return $this->belongsTo(Man::class);
    }
    protected $casts=[
        'pay_who'=>PayWho::class,
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
