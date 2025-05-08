<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Man extends Model
{
    protected $connection = 'other';
    protected $casts=['visible'=>'boolean',];



    public function visibleonly(): Attribute
    {
        return new Attribute(
            get: function( $originalValue ){
               return $originalValue->where('visible',1);
            });

        /**
         * Or alternatively:-
         *
         * return Attribute::get( function( $originalValue ){
         *    // do whatever you want to do
         *    // return $modifiedValue;
         * });
         */
    }

    public function getVisibleonlyAttribute() {
        return $this->where('visible',1);
    }

    public function kyde()
    {
        return $this->morphMany(Kyde::class, 'kydeable');
    }

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
