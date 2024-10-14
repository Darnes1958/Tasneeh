<?php

namespace App\Models;

use App\Enums\ImpExp;
use App\Enums\PayType;
use App\Enums\RecWho;

use App\Enums\RecWhoView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Receipt extends Model
{
    protected $connection = 'other';

    protected $appends = ['tot'];

    public function getTotAttribute()
    {
        return $this->val+$this->differ;
    }
    public function Sell(){
        return $this->belongsTo(Sell::class);
    }
    public function Customer() {
        return $this->belongsTo(Customer::class);
    }


    public function Acc(){
        return $this->belongsTo(Acc::class);
    }

    public function Kazena(){
        return $this->belongsTo(Kazena::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts =[
        'rec_who' => RecWhoView::class,
        'imp_exp' => ImpExp::class,
        'pay_type' => PayType::class,
    ];
}
