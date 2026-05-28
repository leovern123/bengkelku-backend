<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'payment_id',
        'order_id',
        'payment_method',
        'paid_amount',
        'change_amount',
        'payment_status',
        'payment_date',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}