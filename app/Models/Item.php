<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'item_id',
        'item_category_id',
        'item_name',
        'purchase_price',
        'selling_price',
        'stock',
        'image',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id', 'item_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }


    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'item_id', 'item_id');
    }
}