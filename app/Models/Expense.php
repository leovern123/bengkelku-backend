<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'expense_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'expense_id',
        'user_id',
        'expense_name',
        'expense_category',
        'amount',
        'expense_date',
        'note',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}