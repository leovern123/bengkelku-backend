<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    protected $primaryKey = 'order_detail_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_detail_id',
        'order_id',
        'item_id',
        'quantity',
        'purchase_price_at_transaction',
        'selling_price_at_transaction',
        'subtotal',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}