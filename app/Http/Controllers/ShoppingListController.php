<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Product;

class ShoppingListController extends Controller
{

    protected $rules;
    protected $product_rules;
    protected $edit_product_rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|unique:shopping_lists|string',
            'products' => 'array',
            'products.*.id' => 'required|integer|min:1',
            'products.*.quantity' => 'required|integer|min:1'
        ];

        $this->product_rules = [
            'product_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1'
        ];

        $this->edit_product_rules = [
            'quantity' => 'required|integer|min:1'
        ];
    }

    /**
     * @OA\Get(
     *     path="/shopping_lists",
     *     summary="Get a list of Shopping Lists",
     *     tags={"Shopping Lists"},
     *     @OA\Parameter(
     *         name="title",
     *         in="path",
     *         description="Title to be searched ('LIKE' search)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id",type="integer", example="1"),
     *                 @OA\Property(property="title",type="string"),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example="1"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="quantity", type="integer", example="1"),
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
    */

    public function index(Request $request)
    {
        $shopping_lists = ShoppingList::select('id', 'title');

        if ($request->filled('title')) {
            $shopping_lists = $shopping_lists->where('title', 'LIKE', '%'.$request->title.'%');
        }

        $shopping_lists = $shopping_lists->get();

        $data = [];

        foreach ($shopping_lists as $shopping_list) {

            $data[] = [
                'id' => $shopping_list->id,
                'title' => $shopping_list->title,
                'products' => $shopping_list->formatted_products()
            ];
        }

        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *     path="/shopping_lists",
     *     summary="Create a new Shopping List",
     *     tags={"Shopping Lists"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="quantity", type="integer", example="1")
     *                 )
     *             ),
     *             required={"title"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shopping List created succesfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer", example="1"),
     *                 )
     *             )
     *         )
     *     )
     * )
    */

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), $this->rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            } else {
                $shopping_list = ShoppingList::create($request->all());
                $products = [];

                if ($request->filled('products')) {
                    foreach ($request->products as $product) {
                        $item = ShoppingListItem::create([
                            'shopping_list_id' => $shopping_list->id,
                            'product_id' => $product['id'],
                            'quantity' => $product['quantity']
                        ]);
                    }
                }

                return response()->json([
                    'id' => $shopping_list->id,
                    'title' => $shopping_list->title,
                    'products' => $shopping_list->formatted_products()
                ], 200);
            }

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/shopping_lists/{id}",
     *     summary="Get a specific Shopping List",
     *     tags={"Shopping Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Shopping List ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer", example="1"),
     *                 )
     *             )
     *         )
     *     )
     * )
    */

    public function show($id)
    {
        $shopping_list = ShoppingList::find($id);

        if ($shopping_list) {

            return response()->json([
                'id' => $shopping_list->id,
                'title' => $shopping_list->title,
                'products' => $shopping_list->formatted_products()
            ], 200);

        } else {
            return response()->json([
                'error' => 'Shopping List not found'
            ], 404);
        }
    }

    /**
     * @OA\Patch(
     *     path="/shopping_lists/{id}",
     *     summary="Edit an existing Shopping List",
     *     tags={"Shopping Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Shopping List ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="quantity", type="integer", example="1")
     *                 )
     *             ),
     *             required={"title"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shopping List created succesfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer", example="1"),
     *                 )
     *             )
     *         )
     *     )
     * )
    */

    public function update(Request $request, $id)
    {
        try {

            $validator = Validator::make($request->all(), $this->rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            } else {

                $shopping_list = ShoppingList::find($id);

                if ($shopping_list) {
                    $shopping_list->update($request->all());
                    $shopping_list->refresh();

                    return response()->json([
                        'id' => $shopping_list->id,
                        'title' => $shopping_list->title,
                        'products' => $shopping_list->formatted_products()
                    ], 200);
                } else {
                    return response()->json([
                        'error' => 'Shopping List not Found'
                    ], 404);
                }
            }

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/shopping_lists/{id}",
     *     summary="Delete an existing Shopping List",
     *     tags={"Shopping Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Shopping List ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Shopping List created succesfully.",
     *     )
     * )
    */

    public function destroy($id)
    {
        $shopping_list = ShoppingList::find($id);

        if ($shopping_list) {

            $shopping_list = $shopping_list->destroy();
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Shopping List not Found'
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/shopping_lists/{id}/products",
     *     summary="Add a new Product to a Shopping List",
     *     tags={"Shopping Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Shopping List ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="product_id", type="integer", example="1"),
     *             @OA\Property(property="quantity", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added succesfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer", example="1"),
     *                 )
     *             )
     *         )
     *     )
     * )
    */

    public function add_product(Request $request, $id)
    {
        try {
            $shopping_list = ShoppingList::find($id);

            // Find Shopping List
            if ($shopping_list) {

                // Validate request payload
                $validator = Validator::make($request->all(), $this->product_rules);
                if ($validator->fails()) {
                    throw new ValidationException($validator);
                } else {

                    $product = Product::find($request->product_id);

                    // Find Product
                    if ($product) {

                        // Check if Product already exists in Shopping List
                        if ($shopping_list->has_product($product->id)) {
                            return response()->json([
                                'error' => 'Product already in Shopping List'
                            ], 400);
                        } else {
                            $item = ShoppingListItem::create([
                                'shopping_list_id' => $id,
                                'product_id' => $product->id,
                                'quantity' => $request->quantity
                            ]);

                            $shopping_list->refresh();

                            return response()->json([
                                'id' => $shopping_list->id,
                                'title' => $shopping_list->title,
                                'products' => $shopping_list->formatted_products()
                            ], 200);

                        }
                    } else {
                        return response()->json([
                            'error' => 'Product not found'
                        ], 404);
                    }
                }

            } else {
                return response()->json([
                    'error' => "Shopping List not found"
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * @OA\Patch(
     *     path="/shopping_lists/{id}/products/{product_id}",
     *     summary="Edit a Product in a Shopping List",
     *     tags={"Shopping Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Shopping List ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="quantity", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product edited succesfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer", example="1"),
     *                 )
     *             )
     *         )
     *     )
     * )
    */

    public function edit_product(Request $request, $id, $product_id)
    {
        try {

            $shopping_list = ShoppingList::find($id);
            $product = Product::find($product_id);
            $validator = Validator::make($request->all(), $this->edit_product_rules);

            if (!$shopping_list) {
                return response()->json([
                    'error' => 'Shopping List not found'
                ], 404);
            }

            if (!$product) {
                return response()->json([
                    'error' => 'Product not found'
                ],404);
            }

            if (!$shopping_list->has_product($product_id)) {
                return response()->json([
                    'error' => "Product doesn't exist in this Shopping List"
                ], 400);
            }

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $item = ShoppingListItem::where('shopping_list_id', '=', $id)
            ->where('product_id', '=', $product_id)->first();

            $item->update($request->all());
            $shopping_list->refresh();

            return response()->json([
                'id' => $shopping_list->id,
                'title' => $shopping_list->title,
                'products' => $shopping_list->formatted_products()
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
