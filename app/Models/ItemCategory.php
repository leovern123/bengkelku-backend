<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 'item_categories';
    protected $primaryKey = 'item_category_id';

    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'item_category_id',
        'item_type_id',
        'category_name',
    ];

    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'item_type_id', 'item_type_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'item_category_id', 'item_category_id');
    }
}