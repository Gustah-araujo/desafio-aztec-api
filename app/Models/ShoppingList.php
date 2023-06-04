<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="Shopping List",
 *     description="A basic Shopping List",
 *     @OA\Xml(
 *         name="Shopping List"
 *     )
 * )
*/
class ShoppingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
    ];

    /**
     * @OA\Property(
     *     property="id",
     *     description="The Shopping List's unique identifier",
     *     type="integer",
     *     example="1"
     * )

     * @OA\Property(
     *     property="title",
     *     description="The Shopping List's title",
     *     type="string",
     *     example="Reposição de Estoque Semanal"
     * )

     * @OA\Property(
     *     property="items",
     *     description="The Shopping List's items and their quantities",
     *     type="array",
     *     @OA\Items(
     *         @OA\Property(property="product_id", type="integer", example="1"),
     *         @OA\Property(property="product_name", type="string", example="Banana"),
     *         @OA\Property(property="quantity", type="integer", example="1"),
     *     )
     * )
    */

    public function items()
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    public function formatted_products()
    {
        $products = [];

        foreach ($this->items as $_product) {
            $products[] = [
                'id' => $_product->product->id,
                'name' => $_product->product->name,
                'quantity' => $_product->quantity
            ];
        }

        return $products;
    }

    public function has_product($product_id) {
        return ( count($this->items->where('product_id', '=', $product_id)) > 0 );
    }
}
