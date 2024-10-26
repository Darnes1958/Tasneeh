<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class KydeData extends Model
{
    protected $connection = 'other';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company.'Acc';

        }
    }
    public function Kyde()
    {
        return $this->belongsTo(Kyde::class);
    }
    public function Account() {
        return $this->belongsTo(Account::class);
    }
}
