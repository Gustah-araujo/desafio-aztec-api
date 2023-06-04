<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopping_list_id',
        'product_id',
        'quantity'
    ];

    public function shopping_list()
    {
        return $this->belongsTo(ShoppingList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
