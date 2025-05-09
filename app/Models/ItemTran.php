<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ItemTran extends Model
{
    protected $connection = 'other';
    protected $table = 'item_trans';
    protected $primaryKey = false;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
