<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mechanic extends Model
{
    protected $table = 'mechanics';
    protected $primaryKey = 'mechanic_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mechanic_id',
        'mechanic_name',
        'phone_number',
        'address',
        'specialization',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'mechanic_id', 'mechanic_id');
    }
}