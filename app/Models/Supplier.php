<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'supplier_id',
        'supplier_name',
        'phone_number',
        'address',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'supplier_id', 'supplier_id');
    }
}