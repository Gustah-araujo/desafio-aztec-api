<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class ProductController extends Controller
{

    protected $rules;

    public function __construct()
    {
        $this->rules  = [
            'name' => 'required|unique:products|string'
        ];
    }

    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get a list of Products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="search",
     *         in="path",
     *         description="Name search",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id",type="integer"),
     *                     @OA\Property(property="name",type="string")
     *                 )
     *             )
     *         )
     *         ),
     *     ),
     * )
     */

    public function index(Request $request)
    {
        $products = Product::select('id', 'name');

        if ($request->filled('search')) {
            $products = $products->where('name', 'LIKE', '%'.$request->search.'%');
        }


        return response()->json([
            'products' => $products->get()
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create a new Product",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name",type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id",type="integer"),
     *             @OA\Property(property="name",type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="4XX",
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error",type="string")
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
                $product = Product::create($request->all());
                return response()->json([
                    'id' => $product->id,
                    'name' => $product->name
                ],201);
            }

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get a specific Product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Product",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id",type="integer"),
     *             @OA\Property(property="name",type="string")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="4XX",
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error",type="string")
     *         )
     *     )
     * )
     *
     */

    public function show($id)
    {
        $product = Product::find($id);

        if ($product) {
            return response()->json([
                'id' => $product->id,
                'name' => $product->name
            ], 200);
        } else {
            return response()->json([
                'error' => "Product not found"
            ],404);
        }
    }

    /**
     * @OA\Patch(
     *     path="/products/{id}",
     *     summary="Edit an existing Product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Product",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name",type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product edited successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id",type="integer"),
     *             @OA\Property(property="name",type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="4XX",
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error",type="string")
     *         )
     *     )     * )
     */

    public function update(Request $request, $id)
    {
        try {

            $validator = Validator::make($request->all(), $this->rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            } else {

                $product = Product::find($id);

                if ($product) {

                    $product->update($request->all());

                    return response()->json([
                        'id' => $product->id,
                        'name' => $product->name
                    ], 200);

                } else {
                    return response()->json([
                        'error' => "Product not found"
                    ], 404);
                }

            }

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     summary="Delete an existing Product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Product",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(
     *         response="4XX",
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error",type="string")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $product = Product::find($id);

        if ($product) {

            $product->destroy();

            return response()->json([], 204);

        } else {
            return response()->json([
                'error' => "Product not found"
            ], 404);
        }
    }
}
