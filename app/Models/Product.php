<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Product",
 *     description="A basic Product",
 *     @OA\Xml(
 *         name="Product"
 *     )
 * )
*/

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * @OA\Property(
     *     property="id",
     *     description="The Products's unique identifier",
     *     type="integer",
     *     example="1"
     * )

     * @OA\Property(
     *     property="name",
     *     description="The Products's name",
     *     type="string",
     *     example="Banana"
     * )
     */

}
