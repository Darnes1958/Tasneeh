<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Man extends Model
{
    protected $connection = 'other';

    public function account()
    {
        return $this->morphOne(Account::class, 'accountable');
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
