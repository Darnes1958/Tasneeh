<?php

namespace App\Models\views;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Masr_view extends Model
{
  protected $connection = 'other';

  protected $primaryKey = false;
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
