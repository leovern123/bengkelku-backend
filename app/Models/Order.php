<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_id',
        'customer_id',
        'vehicle_id',
        'user_id',
        'mechanic_id',
        'order_code',
        'order_status',
        'total_amount',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class, 'mechanic_id', 'mechanic_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id');
    }
}