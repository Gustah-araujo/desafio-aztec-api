<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="Aztec Desafio API",
 *   version="1.0.0",
 *   @OA\Contact(
 *     email="support@example.com"
 *   )
 * )
 */

class TokenController extends Controller
{


    /**
     * @OA\Post(
     *     path="/token",
     *     summary="Generate Authentication Token",
     *     description="Generate a new Authentication Token to access the API.",
     *     operationId="getToken",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="token", type="string"),
     *         @OA\Property(property="expired_at", type="date", example="01-01-2023")
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
    */

    public function issue_token(Request $request)
    {
        try {
            $plain_token = Str::random(40);
            $expired_at = Carbon::now()->addWeek();

            $token = Token::create([
                'token' => $plain_token,
                'expired_at' => $expired_at
            ]);

            return response()->json([
                'token' => $plain_token,
                'expired_at' => $expired_at->format('d-m-Y')
            ], 200);
        } catch (\Throwable $e) {
            return parent::api_unknown_error();
        }
    }
}
