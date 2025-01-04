<?php

namespace App\Models;

use App\Enums\AccLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Accountsum extends Model
{
    protected $connection = 'other';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company.'Acc';

        }
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $casts=[
        'acc_level'=>AccLevel::class,
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    protected $appends = ['full_name','mden2','daen2'];
    public function getFullNameAttribute(){
        if ($this->acc_level==AccLevel::رئيسي) return  $this->name;
        if ($this->acc_level==AccLevel::فرعي) return  $this->Grand->name.' / '.$this->name;
        if ($this->acc_level==AccLevel::تحليلي) return  $this->Grand->name.' / '.$this->Father->name.' / '.$this->name;
        if ($this->acc_level==AccLevel::تحليلي_مساعد) return  $this->Grand->name.' / '.$this->Father->name.' / '.$this->Son->name.' / '.$this->name;

    }

    public function getMden2Attribute(){
        if ($this->mden>$this->daen) return $this->mden-$this->daen;
        else return 0;
    }
    public function getDaen2Attribute(){
        if ($this->daen>$this->mden) return $this->daen-$this->mden;
        else return 0;
    }
    public function Sons(){
        return $this->hasMany(self::class,'son_id');
    }
    public function Fathers(){
        return $this->hasMany(self::class,'father_id');
    }
    public function Grands(){
        return $this->hasMany(self::class,'grand_id');
    }
    public function Grand(){
        return $this->belongsTo(self::class,'grand_id');
    }
    public function Father(){
        return $this->belongsTo(self::class,'father_id');
    }
    public function Son(){
        return $this->belongsTo(self::class,'son_id');
    }

    public function kyde_data(){
        return $this->hasMany(KydeData::class);
    }

}
