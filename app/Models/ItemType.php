<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $table = 'item_types';
    protected $primaryKey = 'item_type_id';

    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'item_type_id',
        'type_name',
    ];

    public function categories()
    {
        return $this->hasMany(ItemCategory::class, 'item_type_id', 'item_type_id');
    }
}